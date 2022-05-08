<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DefaultTopicsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        //
        $field = [
            'default_topics.id',
            'default_topics.name',
            'default_topics.target',
            'default_topics.description',
            'default_topics.image',
            'default_topics.created_user_id'
        ];
        $default_topics = DB::table('default_topics')->select($field)->simplePaginate(15);
        if (empty($default_topics)) {
            return response()->json([
                'success' => true,
                'messeage' => 'Không có dữ liệu'
            ]);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'topics' => $default_topics,
            ],
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'target' => 'required|max:255',
            'description' => 'required|max:255',
            'image' => 'required|max:255',
            'created_user_id' => 'required|max:255',
        ]);

        $default_topics = DB::table('default_topics')->insertGetId([
            'name' => $request->get('name'),
            'target' => $request->get('target'),
            'description' => $request->get('description'),
            'image' => $request->get('image'),
            'created_user_id' => $request->get('created_user_id')
        ]);
        if (empty($default_topics)) {
            return response()->json([
                'success' => true,
                'message' => 'Đã có lỗi xảy ra, thất bại'
            ], 200);
        }
        return response()->json([
            'success' => true,
            'message' => 'Tạo bộ từ thành công!'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)){
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần nhập vào id'
            ]);
        }
        $isUpdate = DB::table('default_topics')->where('id',$id)->update($request->all());
        if ($isUpdate){
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hành công'
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Không thành công'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $default_topics_id = $request->get('default_topics_id');
        if (empty($default_topics_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần nhập id của chủ đề!'
            ]);
        }
        $topic = DB::table('default_topics')->where('id', $default_topics_id)->get()->toArray();
        if(empty($topic)) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chủ đề!'
            ]);
        }
        if (DB::table('default_topics')->where('id', $default_topics_id)->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa chủ đề thành công!'
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Xóa chủ đề không thành công!'
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showTopicById($id)
    {
        if(empty($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải nhập id của chủ đề!'
            ]);
        }
        $topic = DB::table('default_topics')->where('id', $id)->get()->toArray();
        if (empty($topic)) {
            return response()->json([
                'success' => false,
                'messeage' => 'Không có dữ liệu'
            ]);
        }
        return response()->json([
            'success' => true,
            'topic' => $topic
        ]);
    }
}
