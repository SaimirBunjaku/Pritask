<?php

namespace Database\Factories;

use App\Models\Issue;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    protected static array $catalog = [
        ['Login form throws 500 error on empty password', 'Submitting the login form with an empty password field crashes with an unhandled exception instead of showing a validation message.'],
        ['Add dark mode toggle to settings', 'Users have requested a dark mode option. Add a toggle in the settings page that persists across sessions.'],
        ['Checkout button unresponsive on Safari', "Clicking 'Place Order' on Safari 17 does nothing. Works fine on Chrome and Firefox."],
        ['Refactor authentication middleware', 'Current middleware duplicates logic across three guards. Consolidate into a single reusable class.'],
        ["Mobile nav menu doesn't close after selecting a link", 'On mobile viewports, tapping a nav link navigates correctly but leaves the hamburger menu open underneath.'],
        ['Add CSV export for monthly reports', 'Finance team needs to download the monthly summary as CSV instead of copy-pasting from the table.'],
        ['Image uploads fail silently above 5MB', 'No error message is shown when an upload exceeds the size limit, the spinner just spins forever.'],
        ['Update favicon and touch icons', 'Current favicon is the default placeholder, replace with the actual brand mark.'],
        ['Search results pagination skips a page', "Clicking 'next' from page 1 jumps straight to page 3 in some cases."],
        ['Improve loading state on dashboard widgets', 'Widgets pop in one by one with a layout shift. Add skeleton loaders.'],
        ['Database backups not running on weekends', 'Cron job for nightly backups appears to be skipping Saturday and Sunday.'],
        ['Add confirmation modal before deleting a record', 'Currently delete buttons fire immediately with no confirmation step.'],
        ['Email notifications going to spam', 'Several users report password reset emails landing in the spam folder.'],
        ['Inconsistent button styles across the admin panel', 'Some pages use the old button component, others use the new one. Standardize.'],
        ['API rate limiting too aggressive for bulk imports', 'Bulk import script gets throttled after 30 requests, blocking legitimate batch jobs.'],
        ['Add keyboard shortcuts for power users', 'Support team wants shortcuts for common actions like assign and close.'],
        ["Timezone mismatch in scheduled reports", "Reports show UTC timestamps even though the user's profile timezone is set to EST."],
        ['Broken link in footer to old pricing page', 'Footer still links to /pricing-old which 404s.'],
        ['Add audit log for user role changes', "No record currently exists of who changed a user's permissions or when."],
        ['Slow query on the customer list page', 'Customer list takes 4+ seconds to load once there are more than a few thousand rows.'],
        ['Typo in welcome email subject line', "Subject reads 'Welcome to Pritask !' with an extra space before the exclamation mark."],
        ['Add bulk tag assignment for issues', 'Currently tags must be added one at a time. Would help to select multiple issues and tag them together.'],
        ['Session expires too quickly during long forms', 'Users filling out the onboarding form get logged out mid-way and lose their progress.'],
        ['Support drag-and-drop file attachments', 'Currently the only way to attach a file is through the file picker button.'],
        ['Fix double-submit on the contact form', 'Clicking submit twice quickly creates two separate entries in the database.'],
        ['Add pagination to the activity feed', 'Activity feed currently loads all events at once, gets slow after a few hundred entries.'],
        ['Inconsistent date formats across the app', 'Some pages show MM/DD/YYYY, others show DD/MM/YYYY. Should be consistent.'],
        ['Add retry logic for failed webhook deliveries', 'Webhook calls fail silently if the receiving endpoint is briefly down.'],
        ['Clarify error message when password is too weak', "Current message just says 'invalid password' without explaining the requirements."],
        ['Set up staging environment for QA', 'QA currently tests against production data. Need an isolated staging environment.'],
    ];

    public function definition(): array
    {
        [$title, $description] = fake()->randomElement(self::$catalog);

        return [
            'project_id' => Project::factory(),
            'title' => $title,
            'description' => fake()->boolean(85) ? $description : null,
            'status' => fake()->randomElement(['todo', 'todo', 'todo', 'in_progress', 'in_progress', 'blocked', 'qa_staging', 'qa_done', 'prod', 'prod', 'prod']),
            'priority' => fake()->randomElement(['low', 'medium', 'medium', 'high']),
            'due_date' => fake()->optional(0.5)->dateTimeBetween('now', '+2 months'),
        ];
    }
}
