<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => User::when($request->role_id, function (Builder $query, string $role_id) {
                $query->role($role_id);
            })->get()
        ]);
    }

    public function getById($id)
    {
        $base = User::find($id);

        return response()->json([
            'data' => $base,
        ], 200);
    }
    /**
     * Display a paginated list of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = User::when($request->search, function (Builder $query, string $search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function indexAdmin(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = User::when($request->role_id, function ($q) use ($request) {
            $q->whereHas('roles', function ($query) use ($request) {
                $query->where('id', $request->role_id);
            });
        })->when($request->search, function (Builder $query, string $search) use ($request) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validatedData = $request->validated();

        // if ($request->hasFile('photo')) {
        //     $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        // }

        $user = User::create($validatedData);

        $role = Role::findById($validatedData['role_id']);
        $user->assignRole($role);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $request->id,
            'phone' => 'required|unique:users,phone,' . $request->id,
        ], [
            'email.unique' => 'Email sudah digunakan, silakan gunakan email lain.',
            'phone.unique' => 'Nomor telepon sudah terdaftar, silakan gunakan nomor lain.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $roleId = $request->role_id ?? 2;
        $role = Role::findById($roleId);
        $user->assignRole($role);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user['role_id'] = $user?->role?->id;
        return response()->json([
            'user' => $user
        ]);
    }



    /**
     * Update the specified resource in storage.
     */

    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        } else {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
                $validatedData['photo'] = null;
            }
        }

        $user->update($validatedData);

        $role = Role::findById($validatedData['role_id']);
        $user->syncRoles($role);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function updateMobile(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $data = $request->only('name', 'phone', 'address', 'photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = '/storage/' . $request->file('photo')->store('user', 'public');
        }

        $user = $request->user();
        $user->update($data);

        return response()->json([
            'message' => 'Berhasil memperbarui data',
            'data' => $request->user()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(User $user)
    // {
    //     if ($user->photo) {
    //         Storage::disk('public')->delete($user->photo);
    //     }

    //     $user->delete();

    //     return response()->json([
    //         'success' => true
    //     ]);
    // }

    public function destroy($id)
    {
        $user = User::find($id);
        
        if ($user) {
            // Cek photo setelah memastikan user exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            
            $user->delete();
            return response()->json([
                'status' => 'true',
                'message' => 'Data Berhasil Dihapus'
            ]);
        }
        
        // Return response jika user tidak ditemukan
        return response()->json([
            'status' => 'false',
            'message' => 'Data Tidak Ditemukan'
        ], 404);
    }
    public function updateById(Request $request, $id)
    {

        $base = User::find($id);
        if ($base) {
            $base->update($request->all());

            return response()->json([
                'status' => 'true',
                'message' => 'Data Berhasil Dirubah'
            ]);
        } else {
            return response([
                'message' => 'Data Gagal Dirubah'
            ]);
        }
    }
}
