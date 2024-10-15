<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $links = array(
            array(
                "to" => "/dashboard",
                "icon" => "mdi-view-dashboard",
                "text" => "Tableau de bord",
            ),
            array(
                "to" => "/dashboard/produits",
                "icon" => "mdi-chart-bar",
                "text" => "Gestion des produits",
            ),
            
            array(
                "to" => "/dashboard/vente",
                "icon" => "mdi-cart-percent",
                "text" => "Gestion des Ventes",
            ),
            array(
                "to" => "/dashboard/commandes",
                "icon" => "mdi-heart",
                "text" => "Gestion des commandes",
            ),
            array(
                "to" => "/dashboard/approvisionnement",
                "icon" => "mdi-cogs",
                "text" => "Approvisionnements",
            ),
            array(
                "to" => "/dashboard/inventaire",
                "icon" => "mdi-store",
                "text" => "Inventaire",
                "sublinks" => array(
                    array(
                        "to" => "/dashboard/inventaire/ventes",
                        "icon" => "mdi-store",
                        "text" => "Inventaire des ventes",
                    ),
                    array(
                        "to" => "/dashboard/inventaire/commandes",
                        "icon" => "mdi-store",
                        "text" => "Inventaires commandes",
                    ),
                    array(
                        "to" => "/dashboard/inventaire/entrer",
                        "icon" => "mdi-store",
                        "text" => "Inventaires des entrées",
                    ),
                ),
            ),
            
            array(
                "to" => "/dashboard/utilisateurs",
                "icon" => "mdi-account-star",
                "text" => "Utilisateurs",
                "sublinks" => array(
                    array(
                        "to" => "/dashboard/utilisateurs/gestion",
                        "icon" => "mdi-cogs",
                        "text" => "Gestion des utilisateurs",
                    ),
                    array(
                        "to" => "/dashboard/utilisateurs/role",
                        "icon" => "mdi-cogs",
                        "text" => "Gestion des rôles",
                    ),
                ),
            ),
            array(
                "to" => "/dashboard/parametres",
                "icon" => "mdi-cogs",
                "text" => "Paramètres",
                "sublinks" => array(
                    array(
                        "to" => "/dashboard/parametres/entreprises",
                        "icon" => "mdi-cogs",
                        "text" => "Entreprise",
                    ),
                    array(
                        "to" => "/dashboard/parametres/produits",
                        "icon" => "mdi-cogs",
                        "text" => "Produits",
                    ),
                ),
            ),
        );

        foreach ($links as $link) {
            if (!array_key_exists('sublinks', $link)) {
                $menu = Menu::create($link);
            } else {
                $menu = Menu::create([
                    'to' => $link['to'],
                    'icon' => $link['icon'],
                    'text' => $link['text'],
                ]);
                foreach ($link['sublinks'] as $sub) {
                    SubMenu::create([
                        'to' => $sub['to'],
                        'icon' => $sub['icon'],
                        'menu_id' => $menu->id,
                        'text' => $sub['text'],
                    ]);
                }
            }
        }
    }
}
