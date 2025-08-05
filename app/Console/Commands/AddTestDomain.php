<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Domain;
use App\Models\Widget;
use App\Models\WidgetUrl;

class AddTestDomain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domain:add-test {domain} {--user-id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a test domain for widget verification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domainUrl = $this->argument('domain');
        $userId = $this->option('user-id');

        // Ensure the domain has a protocol
        if (!preg_match('/^https?:\/\//', $domainUrl)) {
            $domainUrl = 'http://' . $domainUrl;
        }

        // Check if domain already exists
        $existingDomain = Domain::where('url', $domainUrl)->first();
        if ($existingDomain) {
            $this->error("Domain {$domainUrl} already exists!");
            return 1;
        }

        // Create the domain
        $domain = Domain::create([
            'url' => $domainUrl,
            'user_id' => $userId,
        ]);

        $this->info("Domain {$domainUrl} created successfully!");

        // Check if there are any widgets to link
        $widgets = Widget::all();
        if ($widgets->count() > 0) {
            if ($this->confirm('Do you want to link this domain to a widget?')) {
                $widgetId = $this->choice(
                    'Select a widget to link:',
                    $widgets->pluck('id', 'name')->toArray()
                );

                // Check if widget URL already exists
                $existingWidgetUrl = WidgetUrl::where('url', $domainUrl)->first();
                if ($existingWidgetUrl) {
                    $this->error("Widget URL for {$domainUrl} already exists!");
                    return 1;
                }

                // Create widget URL
                WidgetUrl::create([
                    'url' => $domainUrl,
                    'widget_id' => $widgetId,
                ]);

                $this->info("Domain linked to widget ID {$widgetId} successfully!");
            }
        } else {
            $this->warn('No widgets found. You may need to create a widget first.');
        }

        return 0;
    }
} 