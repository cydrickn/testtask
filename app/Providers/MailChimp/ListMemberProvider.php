<?php
declare(strict_types=1);

namespace App\Providers\MailChimp;

use App\Services\MailChimp\ListMemberService;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\ServiceProvider;
use Mailchimp\Mailchimp;

/**
 * List member provider
 *
 * @author Cydrick Nonog <cydrick.dev@gmail.com>
 */
class ListMemberProvider extends ServiceProvider
{
    /**
     * Register bindings in the container
     */
    public function register(): void
    {
        $this->app->singleton(ListMemberService::class, function ($app) {
            return new ListMemberService(
                $app->get(EntityManagerInterface::class),
                $app->get(Mailchimp::class),
                $app->get('validator')
            );
        });
    }
}
