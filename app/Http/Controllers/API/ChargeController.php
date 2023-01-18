<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Models\CollectionInvitation;
use App\Models\User;
use Illuminate\Support\Str;

class ChargeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse(Auth::User()->charges, 'Charges');
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
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'total_value' => 'required',
            'number_of_installments' => 'required',
            'payment_day' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Error validation', $validator->errors());
        }
        DB::beginTransaction();
        $user = Auth::User();
        $charge = Charge::create($request->all());
        if (!$charge) {
            DB::rollback();
            return $this->sendError('failed to insert data', ['message' => 'failed to insert data']);
        }
        $charge->users()->attach($user, ["status" => "Creditor"]);

        $installments = [];
        for ($i = 1; $i < $charge->number_of_installments + 1; $i++) {
            $installments[] = ["number" => $i, "value" => number_format($charge->total_value / $charge->number_of_installments, 2, '.', ''), "due_date" => Carbon::now('America/Recife')->day($charge->payment_day)->addMonths($i)->toDateString()];
        }
        try {
            $installments = $charge->installments()->createMany($installments);
        } catch (\Exception $e) {
            return $this->sendError('failed to insert data', ['message' => 'failed to insert data']);
        }
        DB::commit();
        return $this->sendResponse(["charge" => $charge, "installments" => $installments], 'Charge created successfully.', 201);
    }


    /**
     * Associate a debtor to the collection
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function inviteDebtor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'charge_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error validation', $validator->errors());
        }

        $billingParticipants = Auth::user()->charges->loadMissing(['users'])->find($request->charge_id);
        if (!$billingParticipants || count($billingParticipants->users) > 1) {
            return $this->sendError('This collection does not exist or already has a creditor and debtor', ['message' => 'This collection does not exist or already has a creditor and debtor']);
        }

        $inviteCode = Str::uuid()->toString();
        $collectionInvitation = new CollectionInvitation();
        $collectionInvitation->invitation_code = $inviteCode;
        $collectionInvitation->charge_id = $request->charge_id;
        $collectionInvitation->email = $request->email;
        $inviteSaved = $collectionInvitation->save();
        if (!$inviteSaved) {
            return $this->sendError('error creating invitation', ['message' => 'error creating invitation'], 500);
        }

        //
        $isUser = User::where('email', $request->email)->first();
        $user = Auth::user();

        $template = "";
        if ($isUser) {
            // return $this->sendError('This user is already registered in our system', ['message' => 'This user is already registered in our system']);
            $template = <<<EOF
            <h2>Olá {$request->email}</h2>
            <p>Você foi convidado por {$user->name}, para uma cobrança aberta em nosso sistema.</p>
            <p>Visite o link abaixo, para participar da cobrança.</p>
            <p><a src='http://localhost:3000/charge/invitation/{$inviteCode}'>http://localhost:3000/charge/invitation/{$inviteCode}</a></p>
            Obrigado,<br>
            &copy; Copyright 2023, Quitar-Debitos Corporation
            EOF;
        } else {
            $template = <<<EOF
            <h2>Olá {$request->email}</h2>
            <p>Você foi convidado por {$user->name}, para uma cobrança aberta em nosso sistema.</p>
            <p>Visite o link abaixo, para se registrar em nosso sistema e participar da cobrança.</p>
            <p><a src='http://localhost:3000/register/{$inviteCode}'>http://localhost:3000/register/{$inviteCode}</a></p>
            Obrigado,<br>
            &copy; Copyright 2023, Quitar-Debitos Corporation
            EOF;
        }

        try {
            Mail::to($request->email)->send(new SendMail($template));
            return $this->sendResponse('Email successfully sent', 'Charge created successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError('Error sending email', ['message' => $e->getMessage()], 502);
        }
    }


    /**
     * Accept debtor invitation
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function acceptDebtorInvitation(Request $request, $invitation_code)
    {
        DB::beginTransaction();

        $collectionInvitation = CollectionInvitation::where('invitation_code', $invitation_code)->where('status', true)->first();
        if (!$collectionInvitation) {
            return $this->sendError('Invitation does not exist', ['message' => 'Invitation does not exist'], 404);
        }
        $collectionInvitation->status = false;
        $isUpdated = $collectionInvitation->save();
        if (!$isUpdated) {
            return $this->sendError('Failed to change invite code status', ['message' => 'Failed to change invite code status']);
        }

        $user = User::where('email', $collectionInvitation->email)->first();
        if (!$user) {
            return $this->sendError('we can\'t find the guest user', ['message' => 'we can\'t find the guest user']);
        }

        $user->charges()->attach($collectionInvitation->charge, ["status" => "Debtor"]);
        DB::commit();
        return $this->sendResponse('invitation accepted successfully', 'invitation accepted successfully.', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Charge  $charge
     * @return \Illuminate\Http\Response
     */
    public function show(Charge $charge)
    {

        return $this->sendResponse(
            Auth::user()->charges->loadMissing(['installments', 'users'])->find($charge->id),
            'Charge',
            200
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Charge  $charge
     * @return \Illuminate\Http\Response
     */
    public function edit(Charge $charge)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Charge  $charge
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Charge $charge)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Charge  $charge
     * @return \Illuminate\Http\Response
     */
    public function destroy(Charge $charge)
    {
        //
    }
}
