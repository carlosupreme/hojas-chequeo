<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Hojas de Chequeo') }} - Operadores</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            /* Light Theme (Default) */
            --bg-base: #f3f4f6;
            --bg-nav: rgba(255, 255, 255, 0.9);
            --bg-surface: #ffffff;
            --bg-card: rgba(255, 255, 255, 0.7);
            --bg-card-hover: #ffffff;
            --bg-modal: #ffffff;
            --bg-hover-glass: rgba(0, 0, 0, 0.05);
            --bg-overlay: rgba(0, 0, 0, 0.5);

            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-tertiary: #6b7280;
            --text-placeholder: #9ca3af;
            --text-inverse: #ffffff;

            --border-color: rgba(0, 0, 0, 0.1);
            --border-hover: rgba(0, 0, 0, 0.2);

            --color-accent: #E50914;
            --color-accent-hover: #B20710;
            --ring-accent: rgba(229, 9, 20, 0.5);
            --shadow-glow: rgba(229, 9, 20, 0.3);
            --shadow-glow-active: rgba(229, 9, 20, 0.5);
        }

        [data-theme="dark"] {
            --bg-base: #0a0a0a;
            --bg-nav: rgba(10, 10, 10, 0.9);
            --bg-surface: #1a1a1a;
            --bg-card: rgba(26, 26, 26, 0.5);
            --bg-card-hover: #1a1a1a;
            --bg-modal: #141414;
            --bg-hover-glass: rgba(255, 255, 255, 0.1);
            --bg-overlay: rgba(0, 0, 0, 0.8);

            --text-primary: #ffffff;
            --text-secondary: #9ca3af;
            --text-tertiary: #d1d5db;
            --text-placeholder: #6b7280;
            --text-inverse: #ffffff;

            --border-color: rgba(255, 255, 255, 0.1);
            --border-hover: rgba(255, 255, 255, 0.2);

            --color-accent: #E50914;
            --color-accent-hover: #B20710;
            --ring-accent: rgba(229, 9, 20, 0.5);
            --shadow-glow: rgba(229, 9, 20, 0.3);
            --shadow-glow-active: rgba(229, 9, 20, 0.5);
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 20px var(--shadow-glow);
            }
            50% {
                box-shadow: 0 0 40px var(--shadow-glow-active);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.5s ease-out forwards;
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen bg-[var(--bg-base)] text-[var(--text-primary)] antialiased transition-colors duration-300"
      x-data="{
          theme: localStorage.getItem('theme') || 'system',
          themeMenuOpen: false,
          init() {
              this.applyTheme(this.theme);
              this.$watch('theme', (val) => {
                  localStorage.setItem('theme', val);
                  this.applyTheme(val);
              });
              window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                  if (this.theme === 'system') {
                      this.applyTheme('system');
                  }
              });
          },
          applyTheme(val) {
              let isDark = val === 'dark';
              if (val === 'system') {
                  isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
              }
              document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
          }
      }">

<!-- Navigation -->
<nav class="fixed top-0 left-0 right-0 z-40 bg-[var(--bg-nav)] backdrop-blur-xl border-b border-[var(--border-color)] transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-4">
                <img :src="theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) ? '/dark.png' : '/logo.png'" alt="Logo" class="h-8 w-auto">
                <h2 class="text-lg font-semibold text-[var(--text-primary)] hidden sm:block">Hojas de Chequeo</h2>
            </div>
            <div class="flex items-center gap-2">
                <!-- Theme Toggle -->
                <div class="relative" @click.away="themeMenuOpen = false">
                    <button @click="themeMenuOpen = !themeMenuOpen"
                            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-all rounded-lg hover:bg-[var(--bg-hover-glass)]">
                        <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="theme === 'system'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </button>
                    <!-- Dropdown -->
                    <div x-show="themeMenuOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-36 rounded-md shadow-lg bg-[var(--bg-surface)] ring-1 ring-black ring-opacity-5 focus:outline-none z-50 border border-[var(--border-color)]">
                        <div class="py-1">
                            <button @click="theme = 'light'; themeMenuOpen = false" class="group flex w-full items-center px-4 py-2 text-sm text-[var(--text-secondary)] hover:bg-[var(--bg-hover-glass)] hover:text-[var(--text-primary)]">
                                <svg class="mr-3 h-5 w-5 text-[var(--text-muted)] group-hover:text-[var(--text-primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Light
                            </button>
                            <button @click="theme = 'dark'; themeMenuOpen = false" class="group flex w-full items-center px-4 py-2 text-sm text-[var(--text-secondary)] hover:bg-[var(--bg-hover-glass)] hover:text-[var(--text-primary)]">
                                <svg class="mr-3 h-5 w-5 text-[var(--text-muted)] group-hover:text-[var(--text-primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                                Dark
                            </button>
                            <button @click="theme = 'system'; themeMenuOpen = false" class="group flex w-full items-center px-4 py-2 text-sm text-[var(--text-secondary)] hover:bg-[var(--bg-hover-glass)] hover:text-[var(--text-primary)]">
                                <svg class="mr-3 h-5 w-5 text-[var(--text-muted)] group-hover:text-[var(--text-primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                System
                            </button>
                        </div>
                    </div>
                </div>

                <a href="/admin"
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-all rounded-lg hover:bg-[var(--bg-hover-glass)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="hidden md:inline">Administrador</span>
                </a>
                <a href="/supervisor"
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-all rounded-lg hover:bg-[var(--bg-hover-glass)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="hidden md:inline">Supervisor</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="min-h-screen pt-28 pb-12 px-4"
      x-data="{
              selectedUser: null,
              password: '',
              showModal: false,
              error: null,
              loading: false,
              searchQuery: '',
              operadores: {{ Js::from($operadores) }},

              get filteredOperadores() {
                  if (!this.searchQuery.trim()) return this.operadores;
                  const query = this.searchQuery.toLowerCase();
                  return this.operadores.filter(op => op.name.toLowerCase().includes(query));
              },

              getInitials(name) {
                  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
              },

              getAvatarColor(index) {
                  const colors = [
                      'from-red-500 to-red-700',
                      'from-blue-500 to-blue-700',
                      'from-green-500 to-green-700',
                      'from-yellow-500 to-yellow-700',
                      'from-purple-500 to-purple-700',
                      'from-pink-500 to-pink-700',
                      'from-indigo-500 to-indigo-700',
                      'from-teal-500 to-teal-700',
                      'from-orange-500 to-orange-700',
                      'from-cyan-500 to-cyan-700'
                  ];
                  return colors[index % colors.length];
              },

              selectUser(user, index) {
                  this.selectedUser = { ...user, colorIndex: index };
                  this.showModal = true;
                  this.password = '';
                  this.error = null;
                  this.$nextTick(() => {
                      this.$refs.passwordInput?.focus();
                  });
              },

              closeModal() {
                  this.showModal = false;
                  this.selectedUser = null;
                  this.password = '';
                  this.error = null;
              },

              async login() {
                  if (!this.password.trim()) {
                      this.error = 'Ingresa tu contraseña';
                      return;
                  }

                  this.loading = true;
                  this.error = null;

                  try {
                      const response = await fetch('{{ route('login.operador') }}', {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              'X-CSRF-TOKEN': '{{ csrf_token() }}',
                              'Accept': 'application/json'
                          },
                          body: JSON.stringify({
                              user_id: this.selectedUser.id,
                              password: this.password
                          })
                      });

                      const data = await response.json();

                      if (response.ok) {
                          window.location.href = data.redirect || '/operador';
                      } else {
                          this.error = data.message || 'Contraseña incorrecta';
                          this.password = '';
                          this.$refs.passwordInput?.focus();
                      }
                  } catch (e) {
                      this.error = 'Error de conexión. Intenta de nuevo.';
                  } finally {
                      this.loading = false;
                  }
              }
          }">

    <div class="max-w-5xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-10 animate-fade-in-up">
            <h1 class="text-3xl sm:text-4xl font-bold text-[var(--text-primary)] mb-3">¿Quién está trabajando?</h1>
            <p class="text-[var(--text-secondary)] text-lg">Selecciona tu usuario para comenzar</p>
        </div>

        <!-- Search Bar -->
        <div class="max-w-md mx-auto mb-10 animate-fade-in-up" style="animation-delay: 0.1s">
            <div class="relative">
                <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-[var(--text-tertiary)]"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Buscar operador..."
                       class="w-full pl-12 pr-4 py-3 bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-xl text-[var(--text-primary)] placeholder-[var(--text-placeholder)] focus:outline-none focus:ring-2 focus:ring-[var(--ring-accent)] focus:border-[var(--color-accent)] transition-all">
                <button x-show="searchQuery"
                        x-cloak
                        @click="searchQuery = ''"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-[var(--text-tertiary)] hover:text-[var(--text-primary)] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Operators Grid -->
        <div id="operatorGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
            <template x-for="(operador, index) in filteredOperadores" :key="operador.id">
                <button @click="selectUser(operador, index)"
                        class="group flex flex-col items-center p-4 sm:p-6 rounded-2xl bg-[var(--bg-card)] hover:bg-[var(--bg-card-hover)] border border-transparent hover:border-[var(--border-hover)] transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-[var(--ring-accent)] animate-fade-in-up"
                        :style="'animation-delay: ' + (index * 0.05) + 's'">

                    <!-- Avatar -->
                    <div class="relative mb-4">
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-gradient-to-br flex items-center justify-center text-white font-bold text-2xl sm:text-3xl shadow-lg group-hover:shadow-xl transition-all duration-300"
                            :class="getAvatarColor(index)">
                            <span x-text="getInitials(operador.name)"></span>
                        </div>
                        <!-- Online indicator -->
                        <div
                            class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-4 border-[var(--bg-base)] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>

                    <!-- Name -->
                    <span
                        class="text-[var(--text-tertiary)] group-hover:text-[var(--text-primary)] font-medium text-sm sm:text-base text-center transition-colors line-clamp-2"
                        x-text="operador.name"></span>
                </button>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredOperadores.length === 0"
             x-cloak
             class="text-center py-16">
            <svg class="mx-auto h-16 w-16 text-[var(--text-tertiary)] mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-[var(--text-secondary)] text-lg">No se encontraron operadores</p>
            <button @click="searchQuery = ''"
                    class="mt-4 text-[var(--color-accent)] hover:text-[var(--color-accent-hover)] font-medium transition-colors">
                Limpiar búsqueda
            </button>
        </div>
    </div>

    <!-- Password Modal -->
    <div x-show="showModal"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="closeModal()">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-[var(--bg-overlay)] backdrop-blur-sm" @click="closeModal()"></div>

        <!-- Modal Content -->
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-sm bg-[var(--bg-modal)] rounded-2xl border border-[var(--border-color)] shadow-2xl overflow-hidden">

            <!-- Close Button -->
            <button @click="closeModal()"
                    class="absolute top-4 right-4 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors z-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="p-8">
                <!-- User Avatar & Name -->
                <div class="flex flex-col items-center mb-8">
                    <div
                        class="w-24 h-24 rounded-xl bg-gradient-to-br flex items-center justify-center text-white font-bold text-3xl shadow-lg mb-4 pulse-glow"
                        :class="selectedUser ? getAvatarColor(selectedUser.colorIndex) : ''">
                        <span x-text="selectedUser ? getInitials(selectedUser.name) : ''"></span>
                    </div>
                    <h3 class="text-xl font-semibold text-[var(--text-primary)]" x-text="selectedUser?.name"></h3>
                    <p class="text-[var(--text-secondary)] text-sm mt-1">Ingresa tu contraseña</p>
                </div>

                <!-- Password Form -->
                <form @submit.prevent="login()" class="space-y-4">
                    <!-- Error Message -->
                    <div x-show="error"
                         x-cloak
                         x-transition
                         class="flex items-center gap-2 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="error"></span>
                    </div>

                    <!-- Password Input -->
                    <div class="relative">
                        <input type="password"
                               x-ref="passwordInput"
                               x-model="password"
                               @keydown.enter="login()"
                               placeholder="Contraseña"
                               class="w-full px-4 py-4 bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-xl text-[var(--text-primary)] text-center text-lg tracking-widest placeholder-[var(--text-placeholder)] focus:outline-none focus:ring-2 focus:ring-[var(--ring-accent)] focus:border-[var(--color-accent)] transition-all"
                               :class="{ 'border-red-500 focus:ring-red-500/50': error }">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            :disabled="loading || !password"
                            class="w-full py-4 bg-[var(--color-accent)] hover:bg-[var(--color-accent-hover)] disabled:bg-gray-700 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                        <template x-if="loading">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="loading ? 'Ingresando...' : 'Ingresar'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

</body>
</html>