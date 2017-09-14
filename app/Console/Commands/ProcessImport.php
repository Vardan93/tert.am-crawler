<?php

namespace App\Console\Commands;

use Validator;
use GuzzleHttp\Client;
use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class ProcessImport extends Command
{
    /**
     * @var
     */
    private $lastArticles;

    /**
     * @var $data
     */
    private $data;

    /**
     * @var string $baseUrl
     */
    private $baseUrl;

    /**
     * @var
     */
    private $url;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->data = [];

        $this->url = 'http://www.tert.am/am/news/';

        $this->baseUrl = '';

        $this->lastArticles = 1;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pBar = $this->output->createProgressBar(1000);

        Article::query()->delete();
        Storage::delete(
            Storage::allFiles('public/images/')
        );

        $i = 0;
        while (Article::count() < 1000) {
            ($i === 0) ? $this->baseUrl = $this->url : $this->baseUrl = $this->url . $i;
            $this->getArticles($this->baseUrl);
            $i++;
            $pBar->advance();
        }
        $pBar->finish();
    }

    /**
     * @param $url
     */
    private function getArticles($url)
    {
        $cl = new Client();
        $crawler = $cl->request('GET', $url);

        $html = $crawler->getBody()->getContents();
        $tmp = new DomCrawler($html);

        $nodeValues = $tmp->filter('body  #right-col .news-list .news-blocks')
            ->each(function (DomCrawler $node, $i) {
                $aTag = $node->filter('h4 > a')->first();

                $title = $aTag->text();
                $url = $aTag->attr('href');

                return [
                    'title' => $title,
                    'article_url' => $url,
                    'article_id' => last(explode('/', $url)),
                ];
            });

        $this->getAllData($nodeValues);
    }

    /**
     * @param $data
     */
    private function getAllData($data)
    {
        $cl = new Client();
        foreach ($data as $key => $datum) {
            $crawler = $cl->request('GET', $datum['article_url']);

            $html = $crawler->getBody()->getContents();
            $tmp = new DomCrawler($html);

            $nodeValues = $tmp->filter('#item')->each(function (DomCrawler $node, $i) {
                $date = $node->filter('p.n-d');

                ($date->count()) ? $date = $date->first()->text() : $date = '';

                $descriptionArray = $node->filter('#i-content p')->each(function (DomCrawler $node) {
                    $text = $node->text();

                    $text = trim(html_entity_decode($text));
                    $text = stripslashes($text);

                    return ($text === '') ? null : $text;
                });

                $imageUrl = $node->filter('#i-content > img.b-i-i');
                ($imageUrl->count()) ? $imageUrl = $imageUrl->first()->attr('src') : $imageUrl = '';

                $description = '';
                foreach ($descriptionArray as $item) {
                    $description .= $item;
                }

                $image = $this->saveImage($imageUrl);

                return [
                    'description' => $description,
                    'image_url' => $imageUrl,
                    'image' => $image,
                    'date' => $date,
                ];
            });

            $this->data[] = array_merge($datum, $nodeValues[0]);
        }

        foreach ($this->data as $article) {
            $validator = Validator::make($article, [
                'article_id' => 'required|unique:articles'
            ]);

            if ($validator->fails()) {
                Article::whereArticleId($article['article_id'])
                    ->first()
                    ->update($article);
            } else {
                Article::create($article);
            }
        }

        $this->data = [];
    }

    /**
     * @param $imgUlr
     *
     * @return string
     */
    private function saveImage($imgUlr)
    {
        $cl = new Client();

        $imageName = str_random(20) . '.jpg';
        $imagePath = 'public/images/' . $imageName;

        $crawler = $cl->request('GET', $imgUlr);

        try {
            Storage::put($imagePath, $crawler->getBody()->getContents());
        } catch (\Exception $exception) {
            $imageName = '';
        }

        return $imageName;
    }
}
