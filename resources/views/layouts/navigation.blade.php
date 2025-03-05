                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    
                    <!-- Simple Test Link -->
                    <a href="{{ route('items.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                        Test Link
                    </a>
                    
                    <!-- Items Navigation Link -->
                    <x-nav-link :href="route('items.index')" :active="request()->routeIs('items.*')">
                        Items
                    </x-nav-link>
                    
                    <!-- Assemblies Navigation Link -->
                    <x-nav-link :href="route('assemblies.index')" :active="request()->routeIs('assemblies.*')">
                        {{ __('Assemblies') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('packages.index')" :active="request()->routeIs('packages.*')">
                        {{ __('Packages') }}
                    </x-nav-link>

                    <x-nav-link :href="route('estimates.index')" :active="request()->routeIs('estimates.*')">
                        {{ __('Estimates') }}
                    </x-nav-link>

                    <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                        {{ __('Settings') }}
                    </x-nav-link>
                </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-responsive-nav-link>
                    
                    <!-- Responsive Items Navigation Link -->
                    <x-responsive-nav-link :href="route('items.index')" :active="request()->routeIs('items.*')">
                        Items
                    </x-responsive-nav-link>
                    
                    <!-- Responsive Assemblies Navigation Link -->
                    <x-responsive-nav-link :href="route('assemblies.index')" :active="request()->routeIs('assemblies.*')">
                        Assemblies
                    </x-responsive-nav-link>
                    
                    <!-- Responsive Packages Navigation Link -->
                    <x-responsive-nav-link :href="route('packages.index')" :active="request()->routeIs('packages.*')">
                        Packages
                    </x-responsive-nav-link>

                    <!-- Responsive Estimates Navigation Link -->
                    <x-responsive-nav-link :href="route('estimates.index')" :active="request()->routeIs('estimates.*')">
                        Estimates
                    </x-responsive-nav-link>
                    
                    <!-- Responsive Settings Navigation Link -->
                    <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                        Settings
                    </x-responsive-nav-link>
                </div> 