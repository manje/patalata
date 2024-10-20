<div>
        <label for="category" class="block text-sm font-medium text-gray-700">{{ $categoryName }}</label>
            @foreach ($listcategories as $category)
            <div class="form-control">
                <label class="label cursor-pointer">
                <span class="label-text"> 
                    <input type="checkbox" 
                    wire:change="changeCategory({{ $category->id }},$event.target.checked)"
                    @if (in_array($category->id, $selectedCategories))
                        checked
                    @endif
                    value="{{ $category->id }}"> {{ $category->nombre }}</span>
                </label>
            </div>
            @endforeach
        </select>
</div>
