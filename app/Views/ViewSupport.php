<?php

namespace App\Views;

use App\Models\Page;
use App\Views\DTOs\MenuItemDTO;

abstract class ViewSupport {
    /**
     * @return array<MenuItemDTO>
     */
    public static function getMenuItems(): array {
        $response   = [];
        $response[] = new MenuItemDTO(title: 'Database', href: '/database', active: true);
        $response[] = new MenuItemDTO(title: 'Archive', href: '/archive');
        $response[] = new MenuItemDTO(title: 'News', href: '/news');

        $parentPages = Page::where('parent_id', null)->where(function ($q) {
            $q->where('show_in_nav', true)->orWhereNull('show_in_nav');
        })->orderBy('sort_order')->get();

        foreach ($parentPages as $page) {
            $children = [];

            foreach ($page->children->filter(fn ($c) => $c->show_in_nav !== false) as $child) {
                $children[] = new MenuItemDTO(title: $child->title, href: $child->url);
            }

            if ($page->slug === 'get-involved') {
                $children[] = new MenuItemDTO(title: 'Volunteer', href: '/volunteer');
                $children[] = new MenuItemDTO(title: 'Birthdays', href: '/birthdays');
            }

            $response[] = new MenuItemDTO(title: $page->title, href: $page->url, children: empty($children) ? null : $children);
        }

        return $response;
    }
}
