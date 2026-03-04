import React, { useEffect, useRef } from 'react';
import { gsap } from 'gsap';

export default function Profile({ firstname, lastname, email, phone, dob, photo, plan, isVerified, generationUsed, generationLimit }) {
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
        <div style={{ minHeight: '100vh', display: 'flex', flexDirection: 'row', background: '#f9fafb' }}>

            {/* ── GAUCHE : panneau coloré ── */}
            <div
                ref={leftRef}
                style={{
                    width: '40%',
                    flexShrink: 0,
                    background: 'linear-gradient(160deg, #ea580c 0%, #FF701F 60%, #fb923c 100%)',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'space-between',
                    padding: '3rem 2.5rem',
                    opacity: 0,
                }}
            >
                {/* Logo / retour */}
                <div>
                    <a href="/home" style={{ display: 'inline-flex', alignItems: 'center', gap: '0.5rem', color: 'rgba(255,255,255,0.75)', textDecoration: 'none', fontSize: '0.875rem', marginBottom: '3rem' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" style={{ width: '1rem', height: '1rem' }} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Retour à l'accueil
                    </a>

                    {/* Avatar */}
                    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-start', gap: '1rem' }}>
                        {photo ? (
                            <img
                                src={`/uploads/photos/${photo}`}
                                alt="Photo"
                                style={{ width: '5rem', height: '5rem', borderRadius: '50%', border: '3px solid rgba(255,255,255,0.4)', objectFit: 'cover' }}
                            />
                        ) : (
                            <div style={{
                                width: '5rem', height: '5rem', borderRadius: '50%',
                                border: '3px solid rgba(255,255,255,0.4)',
                                background: 'rgba(255,255,255,0.15)',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                            }}>
                                <span style={{ fontFamily: 'Thunder-Extra-Bold, sans-serif', fontSize: '2rem', color: '#fff' }}>{initial}</span>
                            </div>
                        )}

                        <div>
                            <h1 style={{ fontFamily: 'Thunder-Extra-Bold, sans-serif', fontSize: '2.5rem', color: '#fff', lineHeight: 1, margin: '0 0 0.3rem' }}>
                                {fullName}
                            </h1>
                            <p style={{ color: 'rgba(255,255,255,0.65)', fontSize: '0.85rem', margin: 0 }}>{email}</p>
                        </div>

                        {/* Badge plan */}
                        <span style={{
                            background: planStyle.bg,
                            color: planStyle.text,
                            fontSize: '0.75rem', fontWeight: 700,
                            padding: '0.3rem 0.9rem', borderRadius: '999px',
                            border: '1px solid rgba(255,255,255,0.2)',
                        }}>
                            Plan {plan ?? 'FREE'}
                        </span>

                        {/* Badge vérifié */}
                        <div style={{
                            display: 'inline-flex', alignItems: 'center', gap: '0.4rem',
                            background: isVerified ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)',
                            color: isVerified ? '#86efac' : '#fca5a5',
                            fontSize: '0.75rem', fontWeight: 600,
                            padding: '0.3rem 0.75rem', borderRadius: '999px',
                        }}>
                            <svg xmlns="http://www.w3.org/2000/svg" style={{ width: '0.85rem', height: '0.85rem' }} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                                {isVerified
                                    ? <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                    : <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                }
                            </svg>
                            {isVerified ? 'Compte vérifié' : 'Non vérifié'}
                        </div>
                    </div>
                </div>

                {/* Compteur de générations */}
                <div style={{ marginTop: '2rem' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.5rem' }}>
                        <span style={{ fontSize: '0.75rem', color: 'rgba(255,255,255,0.7)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                            Générations
                        </span>
                        <span style={{ fontSize: '0.85rem', fontWeight: 700, color: '#fff' }}>
                            {isUnlimited ? '∞ Illimité' : `${generationUsed} / ${generationLimit}`}
                        </span>
                    </div>
                    {!isUnlimited && (
                        <>
                            <div style={{ background: 'rgba(255,255,255,0.2)', borderRadius: '999px', height: '6px', overflow: 'hidden', marginBottom: '0.5rem' }}>
                                <div style={{ width: `${percent}%`, height: '100%', background: barColor, borderRadius: '999px', transition: 'width 0.4s ease' }} />
                            </div>
                            <p style={{ fontSize: '0.75rem', color: 'rgba(255,255,255,0.6)', margin: 0 }}>
                                {remaining === 0
                                    ? <span style={{ color: '#fca5a5' }}>Limite atteinte — <a href="/plan" style={{ color: '#fff', fontWeight: 700 }}>Passer à un plan supérieur →</a></span>
                                    : `Il vous reste ${remaining} génération${remaining > 1 ? 's' : ''}`
                                }
                            </p>
                        </>
                    )}

                    {/* Stats */}
                    <div style={{ display: 'flex', gap: '2rem', marginTop: '2rem', paddingTop: '1.5rem', borderTop: '1px solid rgba(255,255,255,0.15)' }}>
                        <div>
                            <p style={{ fontFamily: 'Thunder-Extra-Bold, sans-serif', fontSize: '2rem', color: '#fff', lineHeight: 1 }}>
                                {isUnlimited ? '∞' : generationLimit}
                            </p>
                            <p style={{ color: 'rgba(255,255,255,0.5)', fontSize: '0.7rem', marginTop: '0.2rem' }}>Limite du plan</p>
                        </div>
                        <div style={{ width: '1px', background: 'rgba(255,255,255,0.2)' }} />
                        <div>
                            <p style={{ fontFamily: 'Thunder-Extra-Bold, sans-serif', fontSize: '2rem', color: '#fff', lineHeight: 1 }}>{generationUsed}</p>
                            <p style={{ color: 'rgba(255,255,255,0.5)', fontSize: '0.7rem', marginTop: '0.2rem' }}>Utilisées</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* ── DROITE : infos ── */}
            <div
                ref={rightRef}
                style={{
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    padding: '3rem 3rem',
                    background: '#f9fafb',
                    opacity: 0,
                    overflowY: 'auto',
                }}
            >
                <div style={{ maxWidth: '28rem', width: '100%' }}>

                    <h2 style={{ fontFamily: 'Thunder-Extra-Bold, sans-serif', fontSize: '2.5rem', color: '#111827', lineHeight: 1, marginBottom: '0.4rem' }}>
                        MON PROFIL
                    </h2>
                    <p style={{ color: '#9ca3af', fontSize: '0.875rem', marginBottom: '2rem' }}>
                        Vos informations personnelles.
                    </p>

                    {/* Infos personnelles */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem', marginBottom: '2rem' }}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
                            <Field label="Prénom"   value={firstname} />
                            <Field label="Nom"      value={lastname} />
                        </div>
                        <Field label="Email"            value={email} />
                        <Field label="Téléphone"        value={phone} />
                        <Field label="Date de naissance" value={dob} />
                    </div>

                    {/* Abonnement */}
                    <div style={{ background: '#fff', border: '1px solid #f3f4f6', borderRadius: '1rem', padding: '1.25rem', marginBottom: '1.5rem', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '1rem' }}>
                        <div>
                            <p style={{ fontSize: '0.7rem', color: '#9ca3af', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '0.2rem' }}>Plan actuel</p>
                            <p style={{ fontWeight: 700, color: '#111827', fontSize: '1.1rem', margin: 0 }}>{plan ?? 'FREE'}</p>
                        </div>
                        <a href="/plan" style={{
                            display: 'inline-flex', alignItems: 'center', gap: '0.4rem',
                            background: '#FF701F', color: '#fff',
                            fontSize: '0.8rem', fontWeight: 700,
                            padding: '0.6rem 1.1rem', borderRadius: '0.75rem',
                            textDecoration: 'none',
                        }}>
                            Changer de plan
                        </a>
                    </div>

                    {/* Actions */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                        <a href="/generate-pdf" style={{
                            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem',
                            background: '#111827', color: '#fff',
                            fontSize: '0.875rem', fontWeight: 700,
                            padding: '0.875rem', borderRadius: '0.75rem',
                            textDecoration: 'none',
                        }}>
                            <svg xmlns="http://www.w3.org/2000/svg" style={{ width: '1rem', height: '1rem' }} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Aller au convertisseur
                        </a>
                        <a href="/profile/pdf" style={{
                            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem',
                            background: '#fff', color: '#FF701F',
                            border: '1.5px solid #FF701F',
                            fontSize: '0.875rem', fontWeight: 700,
                            padding: '0.875rem', borderRadius: '0.75rem',
                            textDecoration: 'none',
                        }}>
                            <svg xmlns="http://www.w3.org/2000/svg" style={{ width: '1rem', height: '1rem' }} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            Télécharger mon profil PDF
                        </a>
                        <a href="/logout" style={{
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            color: '#9ca3af', fontSize: '0.8rem',
                            textDecoration: 'none', padding: '0.5rem',
                        }}>
                            Déconnexion
                        </a>
                    </div>
                </div>
            </div>

        </div>
    );
}

function Field({ label, value }) {
    return (
        <div>
            <p style={{ fontSize: '0.7rem', fontWeight: 700, color: '#9ca3af', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '0.4rem' }}>
                {label}
            </p>
            <p style={{ background: '#fff', border: '1px solid #f3f4f6', borderRadius: '0.75rem', padding: '0.7rem 1rem', fontSize: '0.875rem', color: value ? '#111827' : '#d1d5db', fontStyle: value ? 'normal' : 'italic', margin: 0 }}>
                {value ?? '—'}
            </p>
        </div>
    );
}
