<?php

namespace App\Http\Controllers\API;

use App\Models\Installment;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstallmentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Installment  $installment
     * @return \Illuminate\Http\Response
     */
    public function show(Installment $installment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Installment  $installment
     * @return \Illuminate\Http\Response
     */
    public function edit(Installment $installment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Installment  $installment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Installment $installment)
    {
        $validator = Validator::make($request->all(), [
            'voucher' => 'mimes:jpg,jpeg,bmp,png,webp|max:2048'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Error validation', $validator->errors());
        }
        $fields = $request->all();
        if (array_key_exists('status', $fields)) {
            $fields['status'] = filter_var($fields['status'], FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($request->hasFile("voucher") && $request->file("voucher")->isValid()) {
            // return $this->sendError('Error validation', ["message" => "erro validation file"]);
            $fileName = $request->file('voucher')->getClientOriginalName();
            $format =  substr($fileName, (strripos($fileName, ".") + 1), (strlen($fileName) - 1));
            $pathName = $request->file('voucher')->getPathname();
            $fileContent = file_get_contents($pathName);
            $filheHash = sha1($fileContent);
            $upload = move_uploaded_file($pathName, base_path() . '/public/uploads/' . $filheHash . '.' . $format);
            if (!$upload) {
                return $this->sendError('Error upload', ["message" => "failed to upload"]);
            }
            $fields['voucher'] = "/uploads/$filheHash.$format";
        }
        $isUpdate = $installment->update($fields);
        if (!$isUpdate) {
            return $this->sendError('Error update', ["message" => "failed to update"]);
        }
        return $this->sendResponse($installment, 'resource updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Installment  $installment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Installment $installment)
    {
        //
    }
}
