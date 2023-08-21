<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('product.index.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="w-full">
                <div class="w-1/2 mb-4 mx-auto">
                    <form action="{{ route('products.search') }}" method="POST">
                        @csrf

                        <input type="text" class="form-control" placeholder="Find product here" name="search" value="{{ old('search') }}">
                        <select data-filter="make" name="category" class="filter-make filter form-control">
                            <option value="0">{{ __('product.index.category') }}</option>
                            <option value="1">{{ __('product.index.food') }}</option>
                            <option value="2">{{ __('product.index.drink') }}</option>
                        </select>
                        <button class="w-1/6 rounded bg-blue-500 p-2 text-white" type="submit">Tìm</button>
                    </form>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    @foreach ($products as $index => $product)
                        <form action="{{ route('cart.store') }}" method="POST">
                            @csrf

                            <div class="w-full h-full bg-white shadow-lg flex flex-col justify-between">
                                <a href="{{ route('products.show', ['product' => $product->id]) }}">
                                    <div class="flex justify-center">
                                        <div class="w-full h-full rounded overflow-hidden">
                                            @if (strpos($product->photo, 'https://via.placeholder.com/') === 0)
                                                <img class="w-full h-56" src="{{ $product->photo }}" alt="Card image">
                                            @else
                                                <img class="w-full h-56" src="{{ asset($product->photo) }}"
                                                    alt="Card image">
                                            @endif
                                            <div class="px-6 py-4">
                                                <input type="hidden" name="id" value="{{ $product->id }}" />
                                                <div class="flex justify-between items-center">
                                                    <div class="font-bold text-xl mb-4">{{ $product->name }}</div>
                                                    <div class="font-bold text-sm text-gray-400 mb-4">
                                                        {{ __('product.index.sold') }}{{ $product->number_of_purchase }}
                                                    </div>
                                                </div>
                                                <div class="text-gray-700 text-base">
                                                    @php
                                                        $limitedDescription = Str::limit($product->description, 100);
                                                    @endphp
                                                    {{ $limitedDescription }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                <div class="flex justify-between">
                                    <div class="px-6 font-bold text-xl text-red-600 mb-4">{{ $product->price }} $</div>
                                    <button class="px-6 mb-4" type="submit">
                                        <i class="fa fa-cart-plus"></i>
                                    </button>
                                </div>

                            </div>
                        </form>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $products->links('pagination::tailwind') }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
