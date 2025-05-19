@props(['darkMode' => false])

<div>
    <div {{ $attributes->class([
              'flex items-center justify-center h-16',
          ]) }}>
                  <span @class([
                      'text-2xl font-bold tracking-tight',
                      'text-red-600' => !$darkMode,
                      'text-red-400' => $darkMode,
                  ])>
                      Wakala
                  </span>
      </div>
</div>
