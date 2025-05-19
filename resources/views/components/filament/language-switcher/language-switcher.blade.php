<div class="flex items-center gap-2">
        <a href="?lang=en"
           @class([
               'px-3 py-1 text-sm rounded',
               'bg-primary-500 text-white' => app()->getLocale() === 'en',
               'text-gray-600 hover:bg-gray-100' => app()->getLocale() !== 'en'
           ])>
           English
        </a>
        <a href="?lang=sw"
           @class([
               'px-3 py-1 text-sm rounded',
               'bg-primary-500 text-white' => app()->getLocale() === 'sw',
               'text-gray-600 hover:bg-gray-100' => app()->getLocale() !== 'sw'
           ])>
           Swahili
        </a>
</div>
