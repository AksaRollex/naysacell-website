<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        $data = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('id', 2);
            })
            ->with(['roles'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

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
        // Validasi data yang diterima
        $validatedData = $request->validated();

        // Cek apakah email sudah ada
        if (User::where('email', $validatedData['email'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email telah digunakan'
            ], 400);
        }

        // Cek apakah nomor telepon sudah ada
        if (User::where('phone', $validatedData['phone'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor telepon telah digunakan'
            ], 400);
        }

        // Buat pengguna baru
        $user = User::create($validatedData);

        // Menetapkan peran untuk pengguna
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

        $user->update($validatedData);

        $role = Role::findById($validatedData['role_id']);
        $user->syncRoles($role);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }


    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => [
                'required',
                'min:8',
                'regex:/^[0-9]+$/',
            ],
            'password_confirmation' => 'required|same:password',
        ], [
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.regex' => 'Password hanya boleh angka',
            'password_confirmation.required' => 'Konfirmasi password harus diisi',
            'password_confirmation.same' => 'Password tidak sama',
        ]);

        $user = User::findOrFail($id);

        try {
            $updated = $user->update([
                'password' => Hash::make($request->password)
            ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password berhasil diperbarui',
                    'data' => $user
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui password'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui password'
            ], 500);
        }
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

    public function destroy($id)
    {

        $user = User::find($id);

        if ($user) {
            $user->delete();
            return response()->json([
                'status' => 'true',
                'message' => 'Data Berhasil Dihapus'
            ]);
        }

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

    public function downloadExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $data = User::get();

        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Role');
        $sheet->setCellValue('D1', 'Email');
        $sheet->setCellValue('E1', 'Nomor Telepon');
        $sheet->setCellValue('F1', 'Alamat');
        $sheet->setCellValue('G1', 'Dibuat Pada');

        $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE1B48F');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:G1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(45);
        $sheet->getColumnDimension('G')->setWidth(40);

        $row = 2;
        foreach ($data as $i => $DepositTransaction) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $DepositTransaction->name);
            $sheet->setCellValue('C' . $row, $DepositTransaction->role->name);
            $sheet->setCellValue('D' . $row, $DepositTransaction->email);
            $sheet->setCellValue('E' . $row, $DepositTransaction->phone);
            $sheet->setCellValue('F' . $row, $DepositTransaction->address);
            $sheet->setCellValue('G' . $row, $DepositTransaction->created_at->format('d-m-Y'));

            $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Laporan Daftar Pengguna.xlsx"');
        $writer->save("php://output");
    }
}
