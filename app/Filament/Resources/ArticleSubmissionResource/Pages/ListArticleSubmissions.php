<?php

namespace App\Filament\Resources\ArticleSubmissionResource\Pages;

use App\Filament\Resources\ArticleSubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListArticleSubmissions extends ListRecords {
    protected static string $resource = ArticleSubmissionResource::class;
}
