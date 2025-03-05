                <x-nav-link :href="route('assemblies.index')" :active="request()->routeIs('assemblies.*')">
                    {{ __('Assemblies') }}
                </x-nav-link>
                
                <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                    {{ __('Settings') }}
                </x-nav-link> 