<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RelatedQuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
        $field = [
            'related_questions.id',
            'related_questions.question',
            'related_questions.vocabulary_id',
            'related_questions.created_user_id',
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
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (empty($request)) {
            return response()->json([
                'success' => true,
                'messeage' => 'Không có dữ liệu'
            ]);
        }
        $request->validate([
            'question' => 'required|max:255',
            'vocabulary_id' => 'required',
            'created_user_id' => 'required',
        ]);

        $related_questions_id = DB::table('related_questions')->insertGetId([
            'question' => $request->get('question'),
            'vocabulary_id' => $request->get('vocabulary_id'),
            'created_user_id' => $request->get('created_user_id'),
        ]);

        if (empty($related_questions_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra, thất bại'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Tạo câu hỏi thành công!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
        $id = $request->get('related_questions_id');
        $isUpdate = DB::table('related_questions')->where('id',$id)->update($request->all());
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
     * @return Response
     */
    public function destroy(Request $request)
    {
        //
        $related_questions_id = $request->get('related_questions_id');
        if (empty($related_questions_id)) {
            return response()->json([
                'success' => true,
                'message' => 'Không tìm thấy'
            ], 200);
        }
        if (DB::table('related_questions')->where('id', $related_questions_id)->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa thành công'
            ], 200);
        }
    }
}
