@if($feedback)
    @php
        $isSuccess = ($feedback['type'] ?? '') === 'success';
        $isWarning = ($feedback['type'] ?? '') === 'warning';
        
        $colorClass = $isSuccess ? 'green' : ($isWarning ? 'red' : 'indigo');
        $bgClass = $isSuccess 
            ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/40 dark:to-emerald-950/20 border-green-500 dark:border-green-400' 
            : ($isWarning ? 'bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/40 dark:to-rose-950/20 border-red-500 dark:border-red-400' : 'bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/40 dark:to-indigo-950/20 border-purple-400 dark:border-indigo-400');
            
        $iconBg = $isSuccess ? 'bg-white/50 border-2 border-white/20' : ($isWarning ? 'bg-amber-100 dark:bg-red-400/20' : 'bg-white/50 dark:bg-indigo-400/20');
    @endphp

    <div class="ai-feedback-gold p-5 rounded-[28px] {{ $bgClass }} backdrop-blur-xl border border-white/20 dark:border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.05)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.2)] relative overflow-hidden group hover:shadow-[0_12px_48px_rgba(212,175,55,0.15)] transition-all duration-500" data-feedback-id="{{ $feedback['id'] ?? '' }}">
        @if($isSuccess)
            <div class="confetti-container absolute inset-0 pointer-events-none" style="z-index: 5; overflow: hidden;">
                @php $colors = ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#F9A8D4']; @endphp
                @for($i=0; $i<40; $i++)
                    @php
                        $left = rand(0, 100);
                        $delay = rand(0, 20) / 10;
                        $duration = 2.5 + (rand(0, 20) / 10);
                        $bg = $colors[$i % 6];
                        $size = rand(5, 10);
                        $shape = rand(0,1) ? '50%' : '2px';
                    @endphp
                    <div class="confetti" style="
                        left: {{ $left }}%; 
                        top: -20px;
                        width: {{ $size }}px; 
                        height: {{ $size }}px; 
                        background-color: {{ $bg }}; 
                        border-radius: {{ $shape }};
                        position: absolute; 
                        animation: confetti-fall {{ $duration }}s ease-in-out infinite;
                        animation-delay: {{ $delay }}s;
                        opacity: 0.8;
                    "></div>
                @endfor
            </div>
        @endif

        <div class="flex items-start gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full {{ $iconBg }} flex items-center justify-center shrink-0 shadow-sm">
                 <span class="text-lg {{ $isSuccess ? 'animate-bounce' : '' }}">
                    {!! $isSuccess ? '🏆' : ($isWarning ? '<i class="bi bi-exclamation-triangle-fill text-amber-600"></i>' : '<i class="bi bi-stars text-indigo-500"></i>') !!}
                 </span>
            </div>
            <div class="flex-1">
                @if($isWarning)
                    <h6 class="font-bold text-slate-800 dark:text-slate-100 text-xs mb-1">
                        <span data-i18n="importantAlert">تنبيه هام</span>
                    </h6>
                @elseif($isSuccess)
                    <h6 class="font-bold text-green-700 dark:text-green-300 text-xs mb-1">
                        <span data-i18n="greatAchievement">مبروك!</span>
                    </h6>
                @else
                         <h6 class="font-bold text-indigo-700 dark:text-indigo-300 text-xs mb-1">
                        <span data-i18n="smartAdvice">رؤية تحليلية</span>
                    </h6>
                @endif

                <p class="text-slate-700 dark:text-slate-200 text-sm leading-relaxed font-medium" 
                   data-i18n="{{ $feedback['key'] ?? 'smartAdvice' }}" 
                   data-i18n-vars='@json($feedback['vars'] ?? [])'>
                    {{ $feedback['message'] ?? '' }}
                </p>
                
                 <div class="flex items-center gap-2 mt-3 opacity-90 group-hover:opacity-100 transition-opacity">
                    @if(!empty($feedback['action']) && !empty($objectType) && $objectType==='goal')
                       <a href="{{ route('goals.edit', $objectId) }}" class="px-3 py-1 bg-white/60 rounded-lg text-[10px] font-bold shadow-sm hover:scale-105 transition-all text-indigo-700">
                            {{ $feedback['action'] }}
                        </a>
                    @endif
                    
                    @if(!empty($feedback['id']))
                        <button type="button" class="px-3 py-1 rounded-lg bg-white text-[10px] font-bold shadow-sm hover:scale-105 active:scale-95 transition-all text-emerald-600" onclick="sendAiFeedback('{{ $feedback['id'] }}','accepted','{{ $objectType }}','{{ $objectId }}', '{{ $feedback['type'] ?? '' }}')">
                            <i class="bi bi-check-lg"></i> <span data-i18n="accept">قبول</span>
                        </button>
                        <button type="button" class="px-3 py-1 rounded-lg bg-white text-[10px] font-bold shadow-sm hover:text-red-500 text-slate-400" onclick="sendAiFeedback('{{ $feedback['id'] }}','dismissed','{{ $objectType }}','{{ $objectId }}', '{{ $feedback['type'] ?? '' }}')">
                           <i class="bi bi-x-lg"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
