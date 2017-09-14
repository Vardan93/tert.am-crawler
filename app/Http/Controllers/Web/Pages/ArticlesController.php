<?php

namespace App\Http\Controllers\Web\Pages;

use App\Models\Article;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Article\Update;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Article\ChangeImage;
use App\Http\Controllers\Web\BaseController;

class ArticlesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $count = Article::count();

        return $this->view('home')->with([
            'count' => $count
        ]);
    }

    /**
     * Get Articles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $addOrEditColumns = [
            'actions',
        ];

        $order = request()->get('order');
        $order = $order[0];
        $orderBy = $order['column'];

        $columns = request()->get('columns');
        $select = [];
        foreach ($columns as $column) {
            $select[] = $column['data'];
        }

        $orderDir = $order['dir'];
        $columnName = $columns[$orderBy]['data'];

        /** @var \Illuminate\Database\Eloquent\Builder $data */
        $data = DB::table('articles');

        $recordsTotal = $data->count();

        $select = array_diff($select, $addOrEditColumns);

        $data = $data->select($select)
            ->skip(request()->get('start'))
            ->limit(request()->get('length'));

        $data = $data->orderBy($columnName, $orderDir)->get();

        $data = $data->each(function ($item) {
            $loader = '<img height="18" src="' . asset('images/loading.gif') . '">';

            $destroyUrl = action('Web\Pages\ArticlesController@destroy', ['id' => $item->id]);

            $btnDelete = '<a data-loading-text=' . "'$loader'" . ' class="btn btn-xs btn-danger btn-delete"';
            $btnDelete .= 'data-url="' . $destroyUrl . '">Delete</a>';

            $showUrl = action('Web\Pages\ArticlesController@show', ['id' => $item->id]);

            $btnShow = '<a data-loading-text=' . "'$loader'" . ' class="btn btn-xs btn-warning btn-show"';
            $btnShow .= 'data-url="' . $showUrl . '">Show</a>';

            $editUrl = action('Web\Pages\ArticlesController@edit', ['id' => $item->id]);
            $updateUrl = action('Web\Pages\ArticlesController@update');

            $btnEdit = '<a data-loading-text=' . "'$loader'" . ' class="btn btn-xs btn-success btn-edit"';
            $btnEdit .= 'data-url="' . $editUrl . '" data-update="' . $updateUrl . '">Edit</a>';

            $actions = $btnEdit . ' ' . $btnShow . ' ' . $btnDelete;

            $item->actions = $actions;
        });

        return response()->json([
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data
        ]);
    }

    /**
     * @param ChangeImage $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeImg(ChangeImage $request)
    {
        $article = Article::find($request->get('id'));

        Storage::delete("public/images/{$article->image}");
        Storage::putFileAs(
            "public/images/",
            $request->file('image'),
            $article->image
        );

        $imagePath = $article->local_img_url;

        return response()->json([
            'image_path' => $imagePath . '?' . now()->timestamp,
            'message' => 'success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Update $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Update $request)
    {
        $data = $request->only([
            'date',
            'title',
            'description'
        ]);
        $article = Article::find($request->get('id'));
        $article->update($data);

        return response()->json([
            'message' => 'success'
        ]);
    }

    /**
     * Return data the specified resource.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var Article $article */
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'message' => '404 not found'
            ], 404);
        }

        return response()->json($article);
    }

    /**
     * Return data for editing the specified resource.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        /** @var Article $article */
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'message' => '404 not found'
            ], 404);
        }

        return response()->json($article);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /** @var Article $article */
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'message' => '404 not found'
            ], 404);
        }

        if ($article->image) {
            Storage::delete('public/images/' . $article->image);
        }

        $article->delete();

        return response()->json([
            'message' => 'success'
        ]);
    }
}
