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
                "to" => "/products",
                "icon" => "mdi-chart-bar",
                "text" => "Produits",
            ),
            array(
                "to" => "/users",
                "icon" => "mdi-account-star",
                "text" => "Utilisateurs",
                "sublinks" => array(
                    array(
                        "to" => "/users",
                        "icon" => "mdi-cogs",
                        "text" => "Listes des utilisateurs",
                    ),
                    array(
                        "to" => "/users/roles",
                        "icon" => "mdi-cogs",
                        "text" => "Gestion des roles",
                    ),
                ),
            ),
            array(
                "to" => "/ventes",
                "icon" => "mdi-cart-percent",
                "text" => "Vente",
            ),
            array(
                "to" => "/inventaires",
                "icon" => "mdi-store",
                "text" => "Inventaire",
                "sublinks" => array(
                    array(
                        "to" => "/inventaires",
                        "icon" => "mdi-store",
                        "text" => "Liste de invetaire",
                    ),
                ),
            ),
            array(
                "to" => "/commandes",
                "icon" => "mdi-heart",
                "text" => "Commandes",
            ),
            array(
                "to" => "/settings",
                "icon" => "mdi-cogs",
                "text" => "Paramètres",
                "sublinks" => array(
                    array(
                        "to" => "/settings",
                        "icon" => "mdi-cogs",
                        "text" => "Produits",
                    ),
                    array(
                        "to" => "/settings/entreprise",
                        "icon" => "mdi-cogs",
                        "text" => "Entreprise",
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
