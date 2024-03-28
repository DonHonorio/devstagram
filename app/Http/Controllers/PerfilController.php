<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('perfil.index');
    }

    public function store(Request $request)
    {
        // Variable que devuelve si el usuario quiere cambiar la contraseña
        $cambiarPassword = isset($request->password);

        // Modificar el Request
        $request->request->add(['username' => Str::slug($request->username)]);

        $this->validate($request, [
            'username' => ['required','unique:users,username,'.auth()->user()->id,'min:3','max:20', 'not_in:twitter,editar-perfil'],
            'email' => ['required','unique:users,email,'.auth()->user()->id,'email','max:60']
        ]);
        
        if($cambiarPassword && !auth()->attempt(['email' => auth()->user()->email, 'password' => $request->password])) {
            return back()->with('mensaje', 'Password Incorrecta');
        }

        if($cambiarPassword) {
            $this->validate($request, [
                'new_password' => 'required|min:6'
            ]);

            if($request->new_password != $request->password_confirmation) return back()->with('mensaje', 'Password de confirmación no coincide');
        }

        if($request->imagen) {

            $imagen = $request->file('imagen');

            $nombreImagen = Str::uuid() . "." . $imagen->extension();

            $imagenServidor = Image::make($imagen);
            $imagenServidor->fit(1000, 1000);

            $imagenPath = public_path('perfiles') . '/' . $nombreImagen;
            $imagenServidor->save($imagenPath);
        }

        // Guardar cambios
        $usuario = User::find(auth()->user()->id);
        $usuario->username = $request->username;
        $usuario->email = $request->email;
        $usuario->imagen = $nombreImagen ?? auth()->user()->imagen ?? null;
        if($cambiarPassword) $usuario->password = $request->new_password;
        $usuario->save();

        // Redireccionar
        return redirect()->route('posts.index', $usuario->username);
    }
}
