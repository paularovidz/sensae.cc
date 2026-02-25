<footer data-footer class="border-t border-border">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div data-footer-col>
                <h3 class="text-lg font-bold text-text-light mb-4 font-secondary">sensaë</h3>
                <p class="text-sm text-text-default">
                    Salle Snoezelen pour des séances de stimulation multisensorielle.
                </p>
            </div>

            <div data-footer-col>
                <h3 class="text-lg font-bold text-text-light mb-4 font-secondary">Contact</h3>
                @isset($contact)
                    @if(!empty($contact['contact_email']))
                        <p class="text-sm mb-2">
                            <a href="mailto:{{ $contact['contact_email'] }}" class="hover:text-primary transition">
                                {{ $contact['contact_email'] }}
                            </a>
                        </p>
                    @endif
                    @if(!empty($contact['contact_phone']))
                        <p class="text-sm mb-2">
                            <a href="tel:{{ $contact['contact_phone'] }}" class="hover:text-primary transition">
                                {{ $contact['contact_phone'] }}
                            </a>
                        </p>
                    @endif
                    @if(!empty($contact['contact_address']))
                        <p class="text-sm">{{ $contact['contact_address'] }}</p>
                    @endif
                @endisset
            </div>

            <div data-footer-col>
                <h3 class="text-lg font-bold text-text-light mb-4 font-secondary">Suivez-nous</h3>
                <div class="flex gap-4">
                    @isset($social)
                        @if(!empty($social['social_facebook']))
                            <a href="{{ $social['social_facebook'] }}" target="_blank" rel="noopener" class="text-text-default hover:text-primary transition">Facebook</a>
                        @endif
                        @if(!empty($social['social_instagram']))
                            <a href="{{ $social['social_instagram'] }}" target="_blank" rel="noopener" class="text-text-default hover:text-primary transition">Instagram</a>
                        @endif
                    @endisset
                </div>
            </div>
        </div>

        <div data-footer-copyright class="mt-8 pt-8 border-t border-border text-center text-sm text-text-default">
            &copy; {{ date('Y') }} sensaë. Tous droits réservés.
        </div>
    </div>
</footer>
