<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\OtherOrganization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class LikendinFrontOrganizationController extends Controller
{
    public function index()
    {
        $organizations = OtherOrganization::orderBy('name')->get();
        return view('organizations.index', compact('organizations'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'vanity_name' => 'required|string|max:255',
                'linkedin_id' => 'required|string|max:255',
                'name' => 'required|string|max:255',
            ]);

            // Vérifier si l'organisation existe déjà
            $existingOrg = OtherOrganization::where('linkedin_id', $request->linkedin_id)
                ->orWhere('vanity_name', $request->vanity_name)
                ->first();

            if ($existingOrg) {
                return redirect()->back()->with('error', 'Cette organisation existe déjà.');
            }

            // Créer la nouvelle organisation
            $organization = OtherOrganization::create([
                'vanity_name' => $request->vanity_name,
                'linkedin_id' => $request->linkedin_id,
                'name' => $request->name,
                'is_active' => true,
            ]);

            // Tenter une synchronisation initiale
            try {
                $linkedinController = app(LinkedInOtherPageController::class);
                $linkedinController->fetchAndStoreOrganizationData(
                    $request->vanity_name, 
                    $request->linkedin_id
                );
                
                $organization->update(['last_synced_at' => now()]);
            } catch (\Exception $e) {
                Log::error("Erreur synchronisation initiale: " . $e->getMessage());
                // Ne pas bloquer l'ajout si la synchro échoue
            }

            return redirect()->route('organizations.index')
                ->with('success', 'Organisation ajoutée avec succès!');

        } catch (\Exception $e) {
            Log::error("Erreur ajout organisation: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'ajout de l\'organisation: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $organization = OtherOrganization::findOrFail($id);
            $organization->is_active = !$organization->is_active;
            $organization->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'is_active' => $organization->is_active
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur changement statut: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut'
            ], 500);
        }
    }

    public function syncNow($id)
    {
        try {
            $organization = OtherOrganization::findOrFail($id);
            
            if (!$organization->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de synchroniser une organisation inactive'
                ], 400);
            }

            $linkedinController = app(LinkedInOtherPageController::class);
            $linkedinController->fetchAndStoreOrganizationData(
                $organization->vanity_name, 
                $organization->linkedin_id
            );
            
            $organization->update(['last_synced_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Synchronisation réussie',
                'last_synced_at' => $organization->last_synced_at->diffForHumans()
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur synchronisation: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la synchronisation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $organization = OtherOrganization::findOrFail($id);
            $organizationName = $organization->name;
            $organization->delete();

            return response()->json([
                'success' => true,
                'message' => "Organisation {$organizationName} supprimée avec succès"
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur suppression organisation: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    public function syncAll()
    {
        try {
            $organizations = OtherOrganization::where('is_active', true)->get();
            $successCount = 0;
            $errorCount = 0;

            foreach ($organizations as $organization) {
                try {
                    $linkedinController = app(LinkedInOtherPageController::class);
                    $linkedinController->fetchAndStoreOrganizationData(
                        $organization->vanity_name, 
                        $organization->linkedin_id
                    );
                    
                    $organization->update(['last_synced_at' => now()]);
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("Erreur synchro {$organization->name}: " . $e->getMessage());
                    $errorCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Synchronisation terminée: {$successCount} réussites, {$errorCount} erreurs"
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur synchro massive: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la synchronisation massive'
            ], 500);
        }
    }
}