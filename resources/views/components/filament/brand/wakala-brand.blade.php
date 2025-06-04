
@props([
    'darkMode' => false, // This prop is passed from AdminPanelProvider.php
])

@php
    // Define the path to your logo image within the 'public' directory.
    // Ensure 'public/images/logo/wakala-logo.jpg' is the correct path to your logo file.
    $logoLightModePath = asset('images/logo/wakala-logo.jpg');

    // Define a separate logo for dark mode IF you have one.
    // If not, the light mode logo will be used for dark mode.
    // For this example, we'll assume the same logo is used unless you specify a different one.
    // $logoDarkModePath = asset('images/logo/wakala-logo-dark.png'); // Example path if you had a dark version
    $logoDarkModePath = $logoLightModePath; // Using the same logo for both modes

    // Determine which logo path to use based on the darkMode prop.
    $currentLogoPath = $darkMode ? $logoDarkModePath : $logoLightModePath;

    // The brand name text.
    $brandName = 'Wakala';
@endphp

{{--
    This div acts as the main container for your brand logo and name.
    It uses Flexbox for alignment.
    - `flex`: Enables flexbox layout.
    - `items-center`: Vertically aligns the logo and text to the center.
    - `space-x-2 rtl:space-x-reverse`: Adds a small horizontal space (0.5rem)
      between the logo and the text. `rtl:space-x-reverse` handles right-to-left layouts correctly.
--}}
<div class="flex items-center space-x-4 rtl:space-x-reverse">

    {{--
        The Logo Image
        - `class="h-12 w-auto object-contain shrink-0 rounded-md"`:
            - `h-12`: Sets the height of the logo to 3rem (12 * 0.25rem = 3rem).
              This is chosen to fit well within a '4rem' brandLogoHeight set in the AdminPanelProvider,
              leaving approximately 0.5rem of padding above and below the image.
              **You can adjust this if needed (e.g., `h-10` for 2.5rem, `h-14` for 3.5rem).**
            - `w-auto`: The width will adjust automatically based on the image's aspect ratio.
            - `object-contain`: Ensures the entire logo is visible within the specified height,
                               scaling it down if necessary without cropping.
            - `shrink-0`: Prevents the image from shrinking if the flex container has limited space.
            - `rounded-md`: Adds medium rounded corners to the logo image. You can change this
                            (e.g., `rounded-sm`, `rounded-lg`, `rounded-full`) or remove it.
    --}}
    <img
        src="{{ $currentLogoPath }}"
        alt="{{ $brandName }} Logo"
        class="h-8 w-auto object-contain shrink-0 rounded-md m-2"
    >

    {{--
        The Brand Name Text ("Wakala")
        - `text-xl`: Sets a larger text size.
        - `font-bold`: Makes the text bold.
        - `tracking-tight`: Adjusts letter spacing for a tighter look.
        - `text-gray-950 dark:text-white`: Sets the text color for light mode (dark gray)
          and dark mode (white) to match Filament's typical theming.
    --}}
      
            <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                {{ $brandName }}
            </span>

</div>
