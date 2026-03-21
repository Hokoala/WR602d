import React, { useEffect, useRef, useState } from 'react';
import { gsap } from 'gsap';
import Header from './Header';
import WhiteBar from './WhiteBar';
import Footer from './Footer';
import GenerationCounter from './GenerationCounter';

function hexToRgba(hex, alpha) {
    if (!hex || hex.length < 7) return `rgba(255,112,31,${alpha})`;
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function ToolCard({ tool, index }) {
    const ref = useRef(null);

    useEffect(() => {
        gsap.fromTo(ref.current,
            { y: 40, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.6, delay: 0.2 + index * 0.12, ease: 'power3.out' }
        );
    }, []);

    const color = tool.color || '#FF701F';
    const isClickable = tool.isActive && tool.hasAccess && tool.route;
    const desc = tool.description || 'Outil de conversion PDF.';

    const handleHover = (e, enter) => {
        if (!isClickable) return;
        gsap.to(e.currentTarget, {
            y: enter ? -8 : 0,
            boxShadow: enter ? '0 20px 50px rgba(0,0,0,0.22)' : '0 8px 32px rgba(0,0,0,0.14)',
            duration: 0.25,
            ease: 'power2.out',
        });
    };

    const getButtonLabel = () => {
        if (!tool.isActive) return 'Bientôt';
        if (!tool.hasAccess) return 'Mettre à niveau';
        return 'Utiliser →';
    };

    const getButtonStyle = () => {
        if (!tool.isActive) return { background: '#e5e7eb', color: '#9ca3af' };
        if (!tool.hasAccess) return { background: '#e5e7eb', color: '#6b7280' };
        return { background: color, color: '#fff' };
    };

    const card = (
        <div
            ref={ref}
            onMouseEnter={e => handleHover(e, true)}
            onMouseLeave={e => handleHover(e, false)}
            style={{
                flex: '1',
                minWidth: '220px',
                maxWidth: '260px',
                background: '#fff',
                borderRadius: '1.5rem',
                padding: '2rem 1.5rem',
                textAlign: 'center',
                boxShadow: '0 8px 32px rgba(0,0,0,0.14)',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                gap: '0.875rem',
                opacity: tool.isActive ? 1 : 0.55,
                cursor: isClickable ? 'pointer' : 'not-allowed',
                textDecoration: 'none',
            }}
        >
            {/* Icône */}
            <div style={{
                width: '4rem',
                height: '4rem',
                borderRadius: '1rem',
                background: hexToRgba(color, 0.12),
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
            }}>
                <i className={tool.icon} style={{ fontSize: '1.75rem', color: color }}></i>
            </div>

            {/* Titre */}
            <h2 style={{ fontSize: '1rem', fontWeight: 700, color: '#111827', lineHeight: 1.3, margin: 0 }}>
                {tool.name}
            </h2>

            {/* Description */}
            <p style={{ fontSize: '0.8rem', color: '#9ca3af', lineHeight: 1.6, margin: 0 }}>
                {desc}
            </p>

            {/* Badge plan requis */}
            {tool.isActive && !tool.hasAccess && (
                <span style={{
                    fontSize: '0.7rem',
                    background: hexToRgba(color, 0.1),
                    color: color,
                    padding: '0.2rem 0.6rem',
                    borderRadius: '0.4rem',
                    fontWeight: 600,
                }}>
                    Plan supérieur requis
                </span>
            )}

            {/* Bouton */}
            <span style={{
                marginTop: 'auto',
                ...getButtonStyle(),
                fontSize: '0.8rem',
                fontWeight: 700,
                padding: '0.5rem 1.25rem',
                borderRadius: '0.75rem',
            }}>
                {getButtonLabel()}
            </span>
        </div>
    );

    return isClickable
        ? <a href={tool.route} style={{ textDecoration: 'none', display: 'contents' }}>{card}</a>
        : card;
}

export default function ConverterHub({ firstname, lastname, email, toolsData, generationUsed, generationLimit, planName, quotaReached }) {
    const titleRef = useRef(null);
    const [showPopup, setShowPopup] = useState(!!quotaReached);

    useEffect(() => {
        gsap.fromTo(titleRef.current,
            { y: 50, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out' }
        );
    }, []);

    const tools = toolsData || [];

    return (
        <div style={{ minHeight: '100vh', display: 'flex', flexDirection: 'column', background: '#FF701F' }}>
            <Header firstname={firstname} lastname={lastname} email={email} />
            <WhiteBar />

            {/* Popup quota atteint */}
            {showPopup && (
                <div style={{
                    position: 'fixed', inset: 0, zIndex: 1000,
                    background: 'rgba(0,0,0,0.5)', backdropFilter: 'blur(4px)',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    padding: '1rem',
                }}>
                    <div style={{
                        background: '#fff', borderRadius: '1.25rem',
                        padding: '2rem', maxWidth: '400px', width: '100%',
                        boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
                        textAlign: 'center',
                    }}>
                        <div style={{
                            width: '3.5rem', height: '3.5rem', borderRadius: '50%',
                            background: '#fef2f2', display: 'flex', alignItems: 'center',
                            justifyContent: 'center', margin: '0 auto 1rem',
                        }}>
                            <i className="fa-solid fa-circle-exclamation" style={{ color: '#ef4444', fontSize: '1.5rem' }} />
                        </div>
                        <h3 style={{ fontWeight: 700, fontSize: '1.1rem', color: '#111827', margin: '0 0 0.5rem' }}>
                            Limite de quota atteinte
                        </h3>
                        <p style={{ fontSize: '0.875rem', color: '#6b7280', margin: '0 0 1.5rem', lineHeight: 1.6 }}>
                            Vous avez utilisé toutes vos générations aujourd'hui (<strong>{generationUsed} / {generationLimit}</strong>).
                            Le quota se renouvelle chaque jour à minuit.
                        </p>
                        <div style={{ display: 'flex', gap: '0.75rem', justifyContent: 'center' }}>
                            <button onClick={() => setShowPopup(false)} style={{
                                padding: '0.6rem 1.2rem', borderRadius: '0.75rem',
                                border: '1px solid #e5e7eb', background: '#fff',
                                fontWeight: 600, fontSize: '0.85rem', cursor: 'pointer', color: '#374151',
                            }}>
                                Fermer
                            </button>
                            <a href="/pricing" style={{
                                padding: '0.6rem 1.2rem', borderRadius: '0.75rem',
                                background: '#FF701F', color: '#fff',
                                fontWeight: 600, fontSize: '0.85rem',
                                textDecoration: 'none', display: 'inline-flex', alignItems: 'center', gap: '0.4rem',
                            }}>
                                <i className="fa-solid fa-arrow-up" />
                                Upgrade
                            </a>
                        </div>
                    </div>
                </div>
            )}

            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '3rem 1.5rem' }}>

                {/* Titre */}
                <div ref={titleRef} style={{ textAlign: 'center', marginBottom: '2rem', opacity: 0 }}>
                    <h1 style={{ fontFamily: 'Thunder-Extra-Bold, sans-serif', fontSize: 'clamp(40px,20vw,420px)', color: '#fff', lineHeight: 1, margin: '0 0 0.5rem' }}>
                        CONVERTISSEUR
                    </h1>
                    <p style={{ color: 'rgba(255,255,255,0.7)', fontSize: '0.95rem', marginBottom: '1.5rem' }}>
                        Choisissez votre outil de conversion
                    </p>
                    <div style={{ display: 'flex', justifyContent: 'center' }}>
                        <GenerationCounter used={generationUsed} limit={generationLimit} planName={planName} />
                    </div>
                </div>

                {/* Cards */}
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '1.5rem', justifyContent: 'center', width: '100%', maxWidth: '1000px' }}>
                    {tools.map((tool, i) => (
                        <ToolCard key={tool.name} tool={tool} index={i} />
                    ))}
                </div>
            </div>

            <Footer />
        </div>
    );
}
