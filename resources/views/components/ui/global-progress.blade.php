{{-- 
    Global Progress Bar Component
    
    Shows a thin progress bar at the top of the page for:
    - Form submissions
    - Page navigations (triggered via custom events)
    
    Include this once in layouts/app.blade.php
--}}

<div id="global-progress" 
     x-data="{ 
         show: false, 
         progress: 0,
         interval: null,
         
         start() {
             this.show = true;
             this.progress = 10;
             // Simulate progress
             this.interval = setInterval(() => {
                 if (this.progress < 90) {
                     this.progress += Math.random() * 15;
                 }
             }, 300);
         },
         
         complete() {
             this.progress = 100;
             clearInterval(this.interval);
             setTimeout(() => {
                 this.show = false;
                 this.progress = 0;
             }, 300);
         }
     }"
     @page-loading-start.window="start()"
     @page-loading-end.window="complete()"
     @form-submitting.window="start()"
     @form-submitted.window="complete()"
     x-show="show"
     x-transition:enter="transition-opacity duration-150"
     x-transition:leave="transition-opacity duration-300"
     x-cloak
     class="fixed top-0 left-0 right-0 z-[9999] h-1">
    <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600 shadow-lg shadow-indigo-500/30 transition-all duration-300 ease-out"
         :style="'width: ' + progress + '%'">
    </div>
</div>
