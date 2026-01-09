 <aside id="logo-sidebar"
     class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
     aria-label="Sidebar">
     <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
         <ul class="space-y-2 font-medium">
             <div class="flex items-center my-4">
                 <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
                 <span class="mx-3 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                     Analitik
                 </span>
                 <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
             </div>
             <li>
                 <a href="#"
                     class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                     <i class="fa-solid fa-chart-line"></i>
                     <span class="ms-3">Dashboard</span>
                 </a>
             </li>
             <div class="flex items-center my-4">
                 <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
                 <span class="mx-3 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                     Pasar
                 </span>
                 <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
             </div>

             <li>
                 <button type="button"
                     class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                     aria-controls="dropdown-pasar" data-collapse-toggle="dropdown-pasar">

                     <i class="fa-solid fa-cart-shopping w-5 text-center"></i>

                     <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">
                        Pasar
                     </span>

                     <i class="fa-solid fa-sort-down"></i>
                  </button>

                 <ul id="dropdown-pasar"
                     class="text-sm hidden py-2 space-y-2">
                     <li>
                         <a wire:navigate href=""
                             class="flex items-center w-full p-2 rounded-lg pl-11 transition duration-75 group
                            text-gray-900 dark:text-white
                            hover:bg-gray-100 dark:hover:bg-gray-700">
                             <i class="fa-solid fa-arrow-right"></i>
                             <span class="ml-2">Bapokting</span>
                         </a>
                     </li>
                     <li>
                         <a wire:navigate href=""
                             class="flex items-center w-full p-2 rounded-lg pl-11 transition duration-75 group
                            text-gray-900 dark:text-white
                            hover:bg-gray-100 dark:hover:bg-gray-700">
                             <i class="fa-solid fa-arrow-right"></i>
                             <span class="ml-2">Sarana Prasarana</span>
                         </a>
                     </li>

                     <li>
                         <a wire:navigate href=""
                             class="flex items-center w-full p-2 rounded-lg pl-11 transition duration-75 group
                            text-gray-900 dark:text-white
                            hover:bg-gray-100 dark:hover:bg-gray-700">
                             <i class="fa-solid fa-arrow-right"></i>
                             <span class="ml-2">Data Pedagang</span>
                         </a>
                     </li>
                 </ul>
             </li>

             <div class="flex items-center my-4">
                 <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
                 <span class="mx-3 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                     Konten
                 </span>
                 <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
             </div>

             <li>
                 <button type="button"
                     class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                     aria-controls="dropdown-posts" data-collapse-toggle="dropdown-posts">
                     <i class="fa-regular fa-pen-to-square"></i>
                     <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Berita</span>
                     <i class="fa-solid fa-sort-down"></i>
                 </button>
                 <ul id="dropdown-posts"
                     class="text-sm {{ request()->routeIs(['dashboard.posts.index','dashboard.posts.create','dashboard.posts.edit','dashboard.categories','dashboard.tags']) ? '' : 'hidden' }} py-2 space-y-2">
                     <li>
                         <a wire:navigate href="{{ route('dashboard.posts.index') }}"
                             class="flex items-center w-full p-2 rounded-lg pl-11 transition duration-75 group
                            text-gray-900 dark:text-white
                            hover:bg-gray-100 dark:hover:bg-gray-700
                            {{ request()->routeIs(['dashboard.posts.index','dashboard.posts.create','dashboard.posts.edit']) ? 'bg-gray-200 dark:bg-gray-600' : '' }}">
                             <i class="fa-solid fa-arrow-right"></i>
                             <span class="ml-2">Manajemen Berita</span>
                         </a>
                     </li>
                     <li>
                         <a wire:navigate href="{{ route('dashboard.categories') }}" class="flex items-center w-full p-2 rounded-lg pl-11 transition duration-75 group
                               text-gray-900 dark:text-white
                               hover:bg-gray-100 dark:hover:bg-gray-700
                               {{ request()->routeIs('dashboard.categories') ? 'bg-gray-200 dark:bg-gray-600' : '' }}">
                             <i class="fa-solid fa-arrow-right"></i>
                             <span class="ml-2">Manajemen Kategori</span>
                         </a>
                     </li>

                     <li>
                         <a wire:navigate href="{{ route('dashboard.tags') }}" class="flex items-center w-full p-2 rounded-lg pl-11 transition duration-75 group
                               text-gray-900 dark:text-white
                               hover:bg-gray-100 dark:hover:bg-gray-700
                               {{ request()->routeIs('dashboard.tags') ? 'bg-gray-200 dark:bg-gray-600' : '' }}">
                             <i class="fa-solid fa-arrow-right"></i>
                             <span class="ml-2">Manajemen Tag</span>
                         </a>
                     </li>


                 </ul>
             </li>
         </ul>



         <ul class="space-y-2 font-medium">
             <div class="flex items-center my-4">
                 <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
                 <span class="mx-3 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                     Pengaturan
                 </span>
                 <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
             </div>

             {{-- SETTINGS --}}
             <li>
                 <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg
                        hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="dropdown-settings"
                     data-collapse-toggle="dropdown-settings">
                     <i class="fa-solid fa-gear"></i>
                     <span class="flex-1 ms-3 text-left whitespace-nowrap">App</span>
                     <i class="fa-solid fa-sort-down"></i>
                 </button>

                 <ul id="dropdown-settings" class="{{ request()->routeIs(
                     'dashboard.roles.*',
                     'dashboard.permissions.*',
                     'dashboard.user-permissions.*',
                     'dashboard.users.index'
                  ) ? '' : 'hidden' }} py-2 space-y-2">

                     {{-- ROLE & PERMISSIONS --}}
                     <li class="text-sm">
                         <button type="button" class="flex items-center w-full p-2 pl-11 text-gray-900 transition duration-75 rounded-lg
                                 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                             aria-controls="dropdown-role-permission" data-collapse-toggle="dropdown-role-permission">
                             <i class="fas fa-users-cog"></i>
                             <span class="flex-1 ms-3 text-left whitespace-nowrap">
                                 Peran & Izin
                             </span>
                             <i class="fa-solid fa-sort-down"></i>
                         </button>

                         <ul id="dropdown-role-permission" class="{{ request()->routeIs(
                              'dashboard.roles.*',
                              'dashboard.permissions.*',
                              'dashboard.user-permissions.*',
                              'dashboard.users.index'
                        ) ? '' : 'hidden' }} py-2 space-y-2">
                             {{-- Roles --}}
                             <li>
                                 <a wire:navigate href="{{ route('dashboard.roles.index') }}"
                                     class="flex items-center w-full p-2 pl-16 rounded-lg transition
                                       text-gray-900 dark:text-white
                                       hover:bg-gray-100 dark:hover:bg-gray-700
                                       {{ request()->routeIs('dashboard.roles.*') ? 'bg-gray-200 dark:bg-gray-600' : '' }}">
                                     <i class="fa-solid fa-arrow-right"></i>
                                     <span class="ml-2">Peran</span>
                                 </a>
                             </li>

                             {{-- Permissions --}}
                             <li>
                                 <a wire:navigate href="{{ route('dashboard.permissions.index') }}"
                                     class="flex items-center w-full p-2 pl-16 rounded-lg transition
                                       text-gray-900 dark:text-white
                                       hover:bg-gray-100 dark:hover:bg-gray-700
                                       {{ request()->routeIs('dashboard.permissions.*') ? 'bg-gray-200 dark:bg-gray-600' : '' }}">
                                     <i class="fa-solid fa-arrow-right"></i>
                                     <span class="ml-2">Izin</span>
                                 </a>
                             </li>

                             {{-- Users Management --}}
                             <li>
                                 <a wire:navigate href="{{ route('dashboard.users.index') }}"
                                     class="flex items-center w-full p-2 pl-16 rounded-lg transition
                                       text-gray-900 dark:text-white
                                       hover:bg-gray-100 dark:hover:bg-gray-700
                                       {{ request()->routeIs('dashboard.users.index') ? 'bg-gray-200 dark:bg-gray-600' : '' }}">
                                     <i class="fa-solid fa-arrow-right"></i>
                                     <span class="ml-2">Akun</span>
                                 </a>
                             </li>
                         </ul>
                     </li>

                 </ul>
             </li>
         </ul>

     </div>
 </aside>
