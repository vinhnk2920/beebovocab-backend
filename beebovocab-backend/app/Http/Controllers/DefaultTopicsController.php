<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DefaultTopicsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $field = [
            'default_topics.id',
            'default_topics.name',
            'default_topics.description',
            'default_topics.image',
            'default_topics.created_user_id'
        ];
        $default_topics = DB::table('default_topics')->get($field);
        if (empty($default_topics)) {
            return response()->json([
                'success' => true,
                'messeage' => 'Không có dữ liệu'
            ], 200);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'vocabularies' => $default_topics,
            ],
        ], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $default_topics = DB::table('default_topics')->insertGetId([
            'id' => $request->get('id'),
            'name' => $request->get('name'),
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
            'message' => 'Thành công'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $id = $request->get('default_topics_id');
        $isUpdate = DB::table('default_topics')->where('id',$id)->update($request->all());
        if($isUpdate){
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hành công'
            ], 200);
        }
        return response()->json([
            'success' => true,
            'message' => 'Không thành công'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $default_topics_id = $request->get('default_topics_id');
        if (empty($default_topics_id)) {
            return response()->json([
                'success' => true,
                'message' => 'Không tìm thấy'
            ], 200);
        }
        if (DB::table('default_topics')->where('id', $default_topics_id)->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa thành công'
            ], 200);
        }
    }
}
