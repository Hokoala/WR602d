import React, { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import Header from './Header';
import WhiteBar from './WhiteBar';

export default function Profile({ firstname, lastname, email, phone, dob, photo, plan, isVerified }) {
    const leftRef  = useRef(null);
    const rightRef = useRef(null);

    useEffect(() => {
        gsap.fromTo(leftRef.current,
            { x: -40, opacity: 0 },
            { x: 0, opacity: 1, duration: 0.7, ease: 'power3.out' }
        );
        gsap.fromTo(rightRef.current,
            { x: 40, opacity: 0 },
            { x: 0, opacity: 1, duration: 0.7, delay: 0.15, ease: 'power3.out' }
        );
    }, []);

    const initial  = (firstname || email || '?')[0].toUpperCase();
    const fullName = [firstname, lastname].filter(Boolean).join(' ') || email;

    return (
        <div style={{ minHeight: '100vh', background: '#FF701F', display: 'flex', flexDirection: 'column' }}>
            <Header firstname={firstname} lastname={lastname} email={email} />
            <WhiteBar />

            <div className="flex-1 px-4 py-10 md:px-10">
                <div className="max-w-5xl mx-auto flex flex-col lg:flex-row gap-6">

                    {/* ── Colonne gauche ── */}
                    <div ref={leftRef} className="lg:w-72 flex-shrink-0 flex flex-col gap-4" style={{ opacity: 0 }}>

                        {/* Carte identité */}
                        <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                            {/* Bandeau */}
                            <div className="h-20" style={{ background: 'linear-gradient(135deg,#ea580c,#FF701F)' }} />
                            {/* Avatar */}
                            <div className="flex flex-col items-center -mt-10 pb-6 px-6">
                                {photo ? (
                                    <img
                                        src={`/uploads/photos/${photo}`}
                                        alt="Photo"
                                        className="w-20 h-20 rounded-full border-4 border-white shadow-md object-cover"
                                    />
                                ) : (
                                    <div className="w-20 h-20 rounded-full border-4 border-white shadow-md bg-orange-100 flex items-center justify-center">
                                        <span className="font-thunder text-3xl text-orange-500">{initial}</span>
                                    </div>
                                )}
                                <h2 className="font-thunder text-[26px] text-gray-900 leading-tight mt-3 text-center">{fullName}</h2>
                                <p className="text-gray-400 text-xs text-center mt-1 break-all">{email}</p>

                                {/* Badge vérifié */}
                                <div className={`mt-3 flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold ${isVerified ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'}`}>
                                    <svg xmlns="http://www.w3.org/2000/svg" className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                                        {isVerified
                                            ? <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                            : <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        }
                                    </svg>
                                    {isVerified ? 'Compte vérifié' : 'Non vérifié'}
                                </div>
                            </div>
                        </div>

                        {/* Bouton PDF */}
                        <a
                            href="/profile/pdf"
                            className="flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-[#FF701F] font-bold px-5 py-4 rounded-2xl shadow-lg transition-all text-sm"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            Télécharger mon PDF
                        </a>
                    </div>

                    {/* ── Colonne droite ── */}
                    <div ref={rightRef} className="flex-1 flex flex-col gap-4" style={{ opacity: 0 }}>

                        {/* Section infos personnelles */}
                        <div className="bg-white rounded-2xl shadow-lg p-6">
                            <h3 className="font-thunder text-[22px] text-gray-800 mb-4 uppercase tracking-wide">
                                Informations personnelles
                            </h3>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Field label="Prénom"           value={firstname} />
                                <Field label="Nom"              value={lastname} />
                                <Field label="Email"            value={email} full />
                                <Field label="Téléphone"        value={phone} />
                                <Field label="Date de naissance" value={dob} />
                            </div>
                        </div>

                        {/* Section abonnement */}
                        <div className="bg-white rounded-2xl shadow-lg p-6">
                            <h3 className="font-thunder text-[22px] text-gray-800 mb-4 uppercase tracking-wide">
                                Abonnement
                            </h3>
                            <div className="flex items-center justify-between gap-4 flex-wrap">
                                <div className="flex items-center gap-4">
                                    <div className="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-400 uppercase tracking-wide">Plan actuel</p>
                                        <p className="font-bold text-gray-800 text-lg">{plan ?? 'Aucun plan'}</p>
                                    </div>
                                </div>

                                <a
                                    href="/profile/update-plan"
                                    className="flex items-center gap-2 bg-[#FF701F] hover:bg-orange-600 text-white font-bold text-sm px-5 py-3 rounded-xl shadow-md transition-all"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Mettre à jour l'abonnement
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    );
}

function Field({ label, value, full }) {
    return (
        <div className={full ? 'sm:col-span-2' : ''}>
            <p className="text-xs text-gray-400 uppercase tracking-wide mb-1">{label}</p>
            <p className="text-gray-800 font-medium text-sm bg-gray-50 rounded-lg px-3 py-2">
                {value ?? <span className="text-gray-300 italic">—</span>}
            </p>
        </div>
    );
}
