{{--
    Delegate category for one author in the confirm-authors step. It is what the fee is
    charged on, and choosing the Student tier marks the author as a student
    (PaperAuthor::booted), so no separate student question is asked. Only the tiers the
    author's country qualifies for are offered.

    $author  the PaperAuthor
--}}
@php
    $allowedCategories = \App\Services\PricingService::allowedCategoriesFor($author->country->name ?? null);
    $categoryOptions = \App\Models\Price::whereIn('category', $allowedCategories)->orderBy('id')->get();
@endphp
<input type="hidden" name="authors[{{ $author->id }}][id]" value="{{ $author->id }}">
<select name="authors[{{ $author->id }}][price_id]" class="form-control form-control-sm" required>
    @foreach($categoryOptions as $option)
        <option value="{{ $option->id }}" {{ (int) $author->price_id === (int) $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
    @endforeach
</select>
