<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = $request->user(); // Utilisateur connecté

        // Si SUPER ADMIN -> Voir toutes les stats globales
        if ($user->hasRole('super_admin')) {
            $totalUsers = User::count();
            $totalProducts = Product::count();
            $totalCategories = Category::count();

            return response()->json([
                'message' => 'Bienvenue sur le tableau de bord super admin',
                'user' => $user,
                'stats' => [
                    'total_users' => $totalUsers,
                    'total_products' => $totalProducts,
                    'total_categories' => $totalCategories,
                ],
            ]);
        }

        // Si PRODUCT MANAGER -> Voir les stats des produits uniquement
        elseif ($user->hasRole('product_manager')) {
            $totalProducts = Product::count(); // Tu peux filtrer selon un champ 'added_by' si nécessaire

            return response()->json([
                'message' => 'Bienvenue sur votre espace product manager',
                'user' => $user,
                'stats' => [
                    'total_products' => $totalProducts,
                ],
            ]);
        }

        // Si USER MANAGER -> Voir uniquement les stats utilisateurs
        elseif ($user->hasRole('user_manager')) {
            $totalUsers = User::count();

            return response()->json([
                'message' => 'Bienvenue sur votre tableau de bord user manager',
                'user' => $user,
                'stats' => [
                    'total_users' => $totalUsers,
                ],
            ]);
        }

        // Par défaut, un message générique
        return response()->json([
            'message' => 'Bienvenue sur votre tableau de bord',
            'user' => $user,
        ]);
    
    }
       
        
    }

