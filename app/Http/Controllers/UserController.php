<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\UserResource;
use App\Models\City;
use App\Models\Countries;
use App\Models\State;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        public readonly UserService $userService
    ) {}


    public function index(Request $request)
    {

        $users = $this->userService->fetchUsers($request);

        return Inertia::render('Users/Index', [
            'users' => UserResource::collection($users),
        ]);
    }

    public function create()
    {
        $roles = Role::all();
        $countries = Countries::all();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'countries' => $countries
        ]);
    }

    public function store(CreateStaffRequest $request)
    {

        $this->userService->storeStaff($request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', 'Staff created successfully!');
    }

    public function update(UpdateStaffRequest $request, User $user)
    {

        $this->userService->updateStaff($request->validated(), $user);

       return redirect()->back()->with('success', 'User updated successfully!');;
    }

    public function show(User $user)
    {
        return Inertia::render('Users/Profile', [
            'user' => new UserResource($user)
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' =>  new UserResource($user),
            'roles' => Role::select('id', 'name')->get(),
            'countries' => Countries::all()
        ]);
    }

     public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'Staff deleted successfully!');
    }

    public function fetchStatesByCountryID(Countries $country)
    {
        $states = State::where('country_id', $country->id)->get();

        return response()->json(['data' => $states]);
    }

    public function fetchCitiesByStateID(State $state)
    {
        $cities = City::where('state_id', $state->id)->get();
        return response()->json(['data' => $cities]);
    }
}
