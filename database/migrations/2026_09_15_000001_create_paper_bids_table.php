<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paper bidding (requirement document, Phase 3): a reviewer marks each paper in their
 * pool Want to Review, Can Review, Neutral or Conflict before assignment.
 *
 * The permission and the on/off setting are added here as well, so an existing
 * database gets the feature without re-running the full seeders. Bidding is marked
 * optional in the document, which is why it can be switched off.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('paper_bids')) {
            Schema::create('paper_bids', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
                $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
                $table->enum('preference', ['want', 'can', 'neutral', 'conflict']);
                $table->timestamps();

                // One standing preference per reviewer per paper; changing it overwrites.
                $table->unique(['paper_id', 'reviewer_id']);
            });
        }

        $permission = Permission::updateOrCreate(['title' => 'review_bid'], ['title' => 'review_bid']);

        // SuperAdmin, Admin and Reviewer.
        foreach ([1, 2, 4] as $roleId) {
            if ($role = Role::find($roleId)) {
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            }
        }

        Setting::firstOrCreate(['key' => 'bidding_enabled'], ['value' => 'true']);
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_bids');
        Permission::where('title', 'review_bid')->delete();
        Setting::where('key', 'bidding_enabled')->delete();
    }
};
