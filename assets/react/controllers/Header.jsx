import React from 'react';

export default function Header() {
    return (
        <header className="p-2 flex items-center justify-between">
            <div className="flex-1">
                <h1 className="text-xl font-bold text-white">LOGO</h1>
            </div>

            <nav className="flex-1 flex justify-center">
                <ul className="flex gap-6">
                    <li><a href="/home" className="text-white hover:text-orange-500">Accueil</a></li>
                    <li><a href="/generate-pdf" className="text-white hover:text-orange-500">Convertisseur</a></li>
                    <li><a href="/plan" className="text-white hover:text-orange-500">Tarifs</a></li>
                    <li><a href="/historique" className="text-white hover:text-orange-500">Historique</a></li>
                    <li><a href="/contact" className="text-white hover:text-orange-500">Contact</a></li>
                </ul>
            </nav>

            <nav className="flex-1 flex justify-end">
                <ul className="flex gap-6">
                    <li><a href="/login" className="text-white hover:text-orange-500">Connexion</a></li>
                    <li><a href="/register" className="text-white hover:text-orange-500">Inscription</a></li>
                </ul>
            </nav>
        </header>
    );
}
