<?php

namespace App\Http\Controllers;

use App\Domain\Model\Sponsor\RunParticipation;
use App\Domain\Model\Sponsor\Sponsor;
use App\Domain\Model\Sponsor\SponsoredRun;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SponsorController extends Controller
{

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($runId)
	{
		$user = Auth::guard()->getUser();
		$run = SponsoredRun::find($runId);
		$runParticipation = RunParticipation::where('sponsored_run_id', $run->id)->where('user_id', $user->id)->first();
		$sponsors = Sponsor::where('run_participation_id', $runParticipation->id)->get();
		return view('sponsors.list')->with('sponsors', $sponsors)->with('run', $run);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($runId)
	{
		$run = SponsoredRun::find($runId);
		return view('sponsors.create')->with('run', $run);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function store(Request $request, $runId)
	{
		$user = Auth::guard()->getUser();
		$run = SponsoredRun::find($runId);
		$runParticipation = RunParticipation::where('sponsored_run_id', $run->id)->where('user_id', $user->id)->first();
		$sponsorData = $request->all();
		$sponsorData['user_id'] = $user->id;
		$sponsorData['run_participation_id'] = $runParticipation->id;
		$sponsor = Sponsor::create($sponsorData);
		return redirect()->route('runpart.sponsor.index', $runId);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($runId, $id)
	{
	return redirect()->route('runpart.sponsor.edit', [$runId, $id]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($runId, $id)
	{
		$sponsor = Sponsor::find($id);
		$run = SponsoredRun::find($runId);
		$runRarticipation = RunParticipation::find($sponsor->run_participation_id);
		$user = Auth::guard()->getUser();
		if ($runRarticipation->user_id != $user->id) {
			return redirect()->route('runpart.sponsor.index', $runId);
		}
		return view('sponsors.edit')->with('sponsor', $sponsor)->with('run', $run);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Request  $request
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $runId, $id)
	{
		$sponsor = Sponsor::find($id);
		$runRarticipation = RunParticipation::find($sponsor->run_participation_id);
		$user = Auth::guard()->getUser();
		if ($runRarticipation->user_id != $user->id) {
			return redirect()->route('sponsor.index');
		}
		$sponsor->firstname = $request->firstname;
		$sponsor->lastname = $request->lastname;
		$sponsor->street = $request->street;
		$sponsor->housenumber = $request->housenumber;
		$sponsor->postcode = $request->postcode;
		$sponsor->city = $request->city;
		$sponsor->phone = $request->phone;
		$sponsor->email = $request->email;
		$sponsor->save();
		return redirect()->route('runpart.sponsor.index', $runId);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($runId, $id)
	{
		$sponsor = Sponsor::find($id);
		$runRarticipation = RunParticipation::find($sponsor->run_participation_id);
		$user = Auth::guard()->getUser();
		if ($runRarticipation->user_id != $user->id) {
			return redirect()->route('runpart.sponsor.index', $runId);
		}
		$sponsor->delete();
		return redirect()->route('runpart.sponsor.index', $runId);
	}

}
