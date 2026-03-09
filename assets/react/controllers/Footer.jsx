import React from 'react';

export default function Footer() {
    return (
        <footer className="w-full py-4 px-6 flex flex-col md:flex-row items-center justify-between text-white/50 text-xs border-t border-white/20">
            <p>2026 &copy; mmi23e10</p>
            <div className="flex gap-4 mt-2 md:mt-0">
                <a href="#" className="hover:text-white transition-colors">Conditions d'utilisation</a>
                <a href="#" className="hover:text-white transition-colors">Politique de confidentialité</a>
                <a href="#" className="hover:text-white transition-colors">Politique des cookies</a>
                <a href="/contact" className="hover:text-white transition-colors">Contact</a>
            </div>
        </footer>
    );
}
