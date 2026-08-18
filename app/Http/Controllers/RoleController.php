<?php

namespace App\Http\Controllers;

use App\Models\Acces;
use App\Models\Collaborateur;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('acces')->orderBy('libelle')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255', 'unique:roles,libelle'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Role::create($validated);

        return redirect()->route('roles.index')->with('success', 'Role cree avec succes.');
    }

    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255', 'unique:roles,libelle,' . $role->id],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role->update($validated);

        return redirect()->route('roles.index')->with('success', 'Role modifie avec succes.');
    }

    public function destroy(Role $role)
    {
        if ($role->acces()->exists()) {
            return back()->with('error', 'Impossible de supprimer un role encore attribue a des collaborateurs.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role supprime avec succes.');
    }

    // Page dediee : gerer les roles d'un collaborateur precis
    public function attribuerForm(Collaborateur $collaborateur)
    {
        $roles = Role::orderBy('libelle')->get();
        $rolesActuels = $collaborateur->roles()->pluck('roles.id')->toArray();

        return view('roles.attribuer', compact('collaborateur', 'roles', 'rolesActuels'));
    }

    public function attribuer(Request $request, Collaborateur $collaborateur)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $dejaAttribue = Acces::where('collaborateur_id', $collaborateur->id)
            ->where('role_id', $validated['role_id'])
            ->exists();

        if ($dejaAttribue) {
            return back()->with('error', 'Ce role est deja attribue a ce collaborateur.');
        }

        Acces::create([
            'collaborateur_id' => $collaborateur->id,
            'role_id' => $validated['role_id'],
            'date_attribution' => now(),
        ]);

        return back()->with('success', 'Role attribue avec succes.');
    }

    public function retirer(Acces $acces)
    {
        $acces->delete();

        return back()->with('success', 'Role retire avec succes.');
    }
}