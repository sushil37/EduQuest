<?php

namespace App\Providers;

use App\Interfaces\BaseRepositoryInterface;
use App\Repositories\BaseRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        // Bind repository interfaces for the specified repositories
        foreach (self::REPO_BINDINGS as $dir => $repos) {
            if (is_array($repos)) {
                foreach ($repos as $repo) {
                    $this->bindRepository($dir, $repo);
                }
            } else {
                // If there is only one repository in the directory
                $this->bindRepository($dir, $repos);
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * dir => [repo, anotherRepo] or dir => repo
     * example of how to set bind repositories
     */
    const REPO_BINDINGS = [
        'Questionnaire' => ['Questionnaire'],
        'Response' => ['Response'],
    ];

    /**
     * Bind repository interfaces for a given directory and repository name.
     *
     * @param string $dir
     * @param string $repo
     */
    private function bindRepository(string $dir,string $repo): void
    {
        $repoInterface = "App\\Interfaces\\{$dir}\\{$repo}RepositoryInterface";
        $repoImplementation = "App\\Repositories\\{$dir}\\{$repo}Repository";

        $this->app->bind($repoInterface, $repoImplementation);
    }
}
