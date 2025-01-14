<x-app-layout>
    <x-slot name="header">{{ __('order.show.title', ['id' => $order->id]) }}</x-slot>

    <div class='py-8'>
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-4 mb-4 bg-white rounded-md">
                <div
                    class="border rounded-md w-full p-2 mb-4 mt-2 @if ($order->orderItems[0]->status === App\Enums\OrderStatus::CANCELED) border-red-500 bg-red-100 @else border-blue-500 @endif">
                    {{ __('constant.orderStatus.' . $order->orderItems[0]->status) }}
                </div>
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 bg-white shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-2">{{ $order->contact->name }}</h2>
                    <p class="text-gray-600 mb-2">{{ __('contact.Address') }}: {{ $order->contact->address }}</p>
                    <p class="text-gray-600 mb-2">{{ __('contact.Phone number') }}: {{ $order->contact->phone_number }}
                    </p>
                </div>
            </div>
            <div class="p-4 mb-4 bg-white rounded-md">
                @foreach ($order->orderItems as $orderItem)
                    <div class="flex mb-4 w-full">
                        <div class="w-1/6 mr-4">
                            @if (strpos($orderItem->product->photo, 'https://via.placeholder.com/') === 0)
                                <img class="object-cover" src="{{ $orderItem->product->photo }}"
                                    alt="{{ $orderItem->product->name }}">
                            @else
                                <img class="object-cover" src="{{ asset($orderItem->product->photo) }}"
                                    alt="{{ $orderItem->product->name }}">
                            @endif
                        </div>
                        <div class="grow">
                            <strong class="text-xl">{{ $orderItem->product->name }}</strong>
                            <div class="flex justify-between mt-4 items-end">
                                <div class="text-opacity-80 text-sm">{{ __('order.Quantity') }}:
                                    {{ $orderItem->quantity }}
                                </div>
                                <div class="text-black text-xl mr-4">
                                    {{ formatCurrency($orderItem->price) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex flex-col justify-end items-end p-4 mb-4 bg-white rounded-md">
                <div class="flex justify-between w-1/3 items-end mb-2">
                    <span class="block text-gray-500">{{ __('order.Payment Method') }}</span>
                    <span
                        class="block text-lg">{{ __('constant.paymentMethod.' . $order->orderItems[0]->payment_method) }}</span>
                </div>
                <div class="flex items-end">
                    <span
                        class="mr-2">{{ __('order.Total', ['totalQuantity' => $order->orderItems->sum('quantity')]) }}:
                    </span>
                    <span class="block text-2xl text-red-500">
                        {{ formatCurrency($order->orderItems->sum('totalPrice')) }}
                    </span>
                </div>
                <div class="flex items-end mt-4">
                    @if (Auth::user()->role === App\Enums\UserRole::ROLE_SALESMAN)
                        <a class="button edit"
                            href="{{ route('salesman.orders.edit', $order) }}">{{ __('Edit') }}</a>
                    @elseif (Auth::user()->role === App\Enums\UserRole::ROLE_ADMIN)
                        <a class="button edit" href="{{ route('admin.orders.edit', $order) }}">{{ __('Edit') }}</a>
                    @else
                        @if ($order->orderItems[0]->status === App\Enums\OrderStatus::WAITING)
                            <a class="button edit" href="{{ route('orders.edit', $order) }}">{{ __('Edit') }}</a>
                        @endif
                    @endif
                    <form action="{{ route('orders.cancel', $order) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button
                            class="ml-4 button  @if ($order->orderItems[0]->status !== App\Enums\OrderStatus::WAITING) disabled @else bg-slate-300 border text-black hover:bg-slate-400 @endif"
                            type="submit">
                            {{ __('order.cancel.button') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
