<?php

namespace App\Http\Controllers;

use App\Domain\Model\Sponsor\RunParticipation;
use App\Domain\Model\Sponsor\SponsoredRun;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;

class AdmRunPartController extends Controller
{

	private $root_route = 'sponrun.';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('role:admin');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(SponsoredRun $sponrun)
	{
		return redirect()->route('sponrun.show', [$sponrun->id]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function store(Request $request, SponsoredRun $sponrun)
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  RunParticipation  $runpart
	 * @return Response
	 */
	public function show(SponsoredRun $sponrun, RunParticipation $runpart)
	{
		return view('runparts.show')->with('runpart', $runpart)
						->with('root_route', $this->root_route)
						->with('root_route_params', [$sponrun->id, $runpart->id])
						->with('breadcrumbs', ['sponrun' => $sponrun, 'runpart' => $runpart]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  RunParticipation  $runpart
	 * @return Response
	 */
	public function edit(SponsoredRun $sponrun, RunParticipation $runpart)
	{
		$projectsSelection = $runpart->sponsoredRun->getProjectSelection();
		return view('runparts.edit')
						->with('projects', $projectsSelection)
						->with('runpart', $runpart)
						->with('adminview', true)
						->with('laps', $runpart->laps)
						->with('root_route', $this->root_route)
						->with('root_route_params', [$sponrun->id, $runpart->id])
						->with('breadcrumbs', ['sponrun' => $sponrun, 'runpart' => $runpart]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Request  $request
	 * @param  RunParticipation  $runpart
	 * @return Response
	 */
	public function update(Request $request, SponsoredRun $sponrun, RunParticipation $runpart)
	{
		$attributes = $request->all();
		$validator = RunParticipation::validator($attributes);
		$validator->validate();

		// check if the Run has already been
		$runpart->fill($attributes);
		$runpart->save();
		
		if ($request->wantsJson()) {
			return response()->json(new \App\Http\Resources\RunParticipation($runpart));
		}
		else {
			Session::flash('messages-success', new MessageBag(["Erfolgreich gespeichert"]));
			return redirect()->route('sponrun.runpart.edit', [$sponrun->id, $runpart->id]);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  RunParticipation  $runpart
	 * @return Response
	 */
	public function destroy(SponsoredRun $sponrun, RunParticipation $runpart)
	{
		$runpart->delete();
		return redirect()->route('sponrun.show', [$sponrun->id]);
	}

}
