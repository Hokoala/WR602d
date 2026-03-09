import React, { useState } from 'react';

export default function Header({ firstname, lastname, email }) {
    const [menuOpen, setMenuOpen] = useState(false);
    const isLoggedIn = !!email;
    const displayName = [firstname, lastname].filter(Boolean).join(' ') || email;

    return (
        <header className="p-2 flex items-center justify-between relative">
            {/* Logo */}
            <div className="flex-1">
                <a href="/home" className="flex items-center gap-2">
                    <img src="/images/logo.svg" alt="Docly" className="h-8" />
                    <span className="text-white font-bold text-xl">Docly</span>
                </a>
            </div>

            {/* Nav desktop */}
            <nav className="hidden md:flex flex-1 justify-center">
                <ul className="flex gap-6">
                    <li><a href="/home" className="text-white hover:text-black">Accueil</a></li>
                    <li><a href="/generate-pdf" className="text-white hover:text-black">Convertisseur</a></li>
                    <li><a href="/plan" className="text-white hover:text-black">Tarifs</a></li>
                    <li><a href="/historique" className="text-white hover:text-black">Historique</a></li>
                    <li><a href="/contact" className="text-white hover:text-black">Contact</a></li>
                </ul>
            </nav>

            {/* Auth desktop */}
            <nav className="hidden md:flex flex-1 justify-end">
                {isLoggedIn ? (
                    <ul className="flex gap-6 items-center">
                        <li>
                            <a href="/profile" className="flex items-center gap-2 text-white hover:text-black font-semibold">
                                <div className="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">
                                    {displayName[0].toUpperCase()}
                                </div>
                                {displayName}
                            </a>
                        </li>
                        <li><a href="/logout" className="text-white/70 hover:text-black text-sm">Déconnexion</a></li>
                    </ul>
                ) : (
                    <ul className="flex gap-6">
                        <li><a href="/login" className="text-white hover:text-black">Connexion</a></li>
                        <li><a href="/register" className="text-white hover:text-black">Inscription</a></li>
                    </ul>
                )}
            </nav>

            {/* Bouton hamburger mobile */}
            <button
                className="md:hidden text-white focus:outline-none"
                onClick={() => setMenuOpen(!menuOpen)}
                aria-label="Menu"
            >
                {menuOpen ? (
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                ) : (
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                )}
            </button>

            {/* Menu mobile déroulant */}
            {menuOpen && (
                <div className="absolute top-full left-0 w-full bg-[#FF701F] z-50 shadow-lg md:hidden">
                    <ul className="flex flex-col px-4 py-3 gap-3">
                        <li><a href="/home" className="text-white hover:text-black block py-1">Accueil</a></li>
                        <li><a href="/generate-pdf" className="text-white hover:text-black block py-1">Convertisseur</a></li>
                        <li><a href="/plan" className="text-white hover:text-black block py-1">Tarifs</a></li>
                        <li><a href="/historique" className="text-white hover:text-black block py-1">Historique</a></li>
                        <li><a href="/contact" className="text-white hover:text-black block py-1">Contact</a></li>
                        <li className="border-t border-white/20 pt-2">
                            {isLoggedIn ? (
                                <>
                                    <a href="/profile" className="text-white hover:text-black block py-1 font-semibold">{displayName}</a>
                                    <a href="/logout" className="text-white/70 hover:text-black block py-1">Déconnexion</a>
                                </>
                            ) : (
                                <>
                                    <a href="/login" className="text-white hover:text-black block py-1">Connexion</a>
                                    <a href="/register" className="text-white hover:text-black block py-1">Inscription</a>
                                </>
                            )}
                        </li>
                    </ul>
                </div>
            )}
        </header>
    );
}
