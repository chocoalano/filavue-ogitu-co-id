<?php

namespace Database\Factories;

use App\Enums\BugPriority;
use App\Enums\BugReporterType;
use App\Enums\BugSeverity;
use App\Enums\BugSource;
use App\Enums\BugStatus;
use App\Enums\MobileType;
use App\Enums\Platform;
use App\Enums\WebScreen;
use App\Models\BugReport;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BugReport>
 */
class BugReportFactory extends Factory
{
    public function definition(): array
    {
        $platform = $this->faker->randomElement(Platform::cases());
        $isWeb = $platform === Platform::Web;

        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
        $browserVersions = ['120.0', '121.0', '122.0', '115.0', '109.0'];

        return [
            'reporter_type' => BugReporterType::Anonymous,
            'reporter_id' => null,
            'reporter_name' => $this->faker->name(),
            'reporter_email' => $this->faker->safeEmail(),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'steps_to_reproduce' => implode("\n", $this->faker->sentences(4)),
            'expected_behavior' => $this->faker->sentence(),
            'actual_behavior' => $this->faker->sentence(),
            'platform' => $platform,
            'source' => $this->faker->randomElement(BugSource::cases()),
            'web_screen' => $isWeb ? $this->faker->randomElement(WebScreen::cases()) : null,
            'mobile_type' => ! $isWeb ? $this->faker->randomElement(MobileType::cases()) : null,
            'page_url' => $this->faker->url(),
            'browser' => $isWeb ? $this->faker->randomElement($browsers) : null,
            'browser_version' => $isWeb ? $this->faker->randomElement($browserVersions) : null,
            'os' => $this->faker->randomElement(['Windows 11', 'macOS 14', 'Ubuntu 22.04', 'Android 14', 'iOS 17']),
            'os_version' => $this->faker->numerify('##.#.#'),
            'device_model' => ! $isWeb ? $this->faker->randomElement(['Samsung Galaxy S24', 'iPhone 15 Pro', 'Xiaomi 14', 'OPPO Reno 11']) : null,
            'app_version' => $this->faker->numerify('#.#.#'),
            'screen_resolution' => $this->faker->randomElement(['1920x1080', '1366x768', '2560x1440', '390x844', '412x915']),
            'severity' => $this->faker->randomElement(BugSeverity::cases()),
            'priority' => $this->faker->randomElement(BugPriority::cases()),
            'status' => BugStatus::Open,
            'duplicate_of_id' => null,
            'assigned_to' => null,
            'resolution_note' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }

    /**
     * State: dilaporkan oleh customer terdaftar.
     */
    public function byCustomer(): static
    {
        return $this->state(fn () => [
            'reporter_type' => BugReporterType::Customer,
            'reporter_id' => Customer::factory(),
            'reporter_name' => null,
            'reporter_email' => null,
        ]);
    }

    /**
     * State: dilaporkan oleh user internal (admin/QA).
     */
    public function byUser(): static
    {
        return $this->state(fn () => [
            'reporter_type' => BugReporterType::User,
            'reporter_id' => User::factory(),
            'reporter_name' => null,
            'reporter_email' => null,
        ]);
    }

    /**
     * State: bug dari platform web.
     */
    public function web(): static
    {
        return $this->state(fn () => [
            'platform' => Platform::Web,
            'web_screen' => $this->faker->randomElement(WebScreen::cases()),
            'mobile_type' => null,
            'device_model' => null,
        ]);
    }

    /**
     * State: bug dari platform mobile.
     */
    public function mobile(): static
    {
        return $this->state(fn () => [
            'platform' => Platform::Mobile,
            'web_screen' => null,
            'mobile_type' => $this->faker->randomElement(MobileType::cases()),
            'browser' => null,
            'browser_version' => null,
        ]);
    }

    /**
     * State: bug kritis yang perlu segera ditangani.
     */
    public function critical(): static
    {
        return $this->state(fn () => [
            'severity' => BugSeverity::Critical,
            'priority' => BugPriority::Urgent,
        ]);
    }

    /**
     * State: bug sudah resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => BugStatus::Resolved,
            'resolved_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'resolution_note' => $this->faker->sentence(),
        ]);
    }

    /**
     * State: bug ditutup.
     */
    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => BugStatus::Closed,
            'resolved_at' => $this->faker->dateTimeBetween('-60 days', '-10 days'),
            'closed_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
            'resolution_note' => $this->faker->sentence(),
        ]);
    }
}
