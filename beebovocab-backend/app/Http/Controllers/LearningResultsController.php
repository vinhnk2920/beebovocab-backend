<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LearningResultsController extends Controller
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
            'related_questions.vocabulary_id',
            'related_questions.user_id',
            'related_questions.f_rate',
        ];
        $related_questions = DB::table('related_questions')->get($field);
        if (empty($related_questions)) {
            return response()->json([
                'success' => true,
                'messeage' => 'Không có dữ liệu'
            ], 200);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'vocabulary' => $related_questions,
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
        if (empty($request)) {
            return response()->json([
                'success' => true,
                'messeage' => 'Không có dữ liệu'
            ], 200);
        }
        $learning_results_id = DB::table('related_questions')->insertGetId([
            'vocabulary_id' => $request->get('title'),
            'user_id' => $request->get('description'),
            'f_rate' => $request->get('avatar_image'),
        ]);
        if (empty($learning_results_id)) {
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
        $id = $request->get('learning_results_id');
        $isUpdate = DB::table('learning_results')->where('id',$id)->update($request->all());
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
        $learning_results_id = $request->get('learning_results_id');
        if (empty($learning_results_id)) {
            return response()->json([
                'success' => true,
                'message' => 'Không tìm thấy từ'
            ], 200);
        }
        if (DB::table('learning_results')->where('id', $learning_results_id)->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa thành công'
            ], 200);
        }
    }
}
