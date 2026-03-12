import React, { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import Header from './Header';
import WhiteBar from './WhiteBar';
import Footer from './Footer';

const TOOL_INFO = {
    'URL to PDF':       { icon: 'fa-solid fa-link',         color: '#FF701F', label: 'URL to PDF',       desc: 'Conversion depuis une URL' },
    'HTML to PDF':      { icon: 'fa-solid fa-code',         color: '#3B82F6', label: 'HTML to PDF',      desc: 'Conversion depuis du HTML' },
    'Merge PDF':        { icon: 'fa-solid fa-object-group', color: '#10B981', label: 'Fusion PDF',       desc: 'Plusieurs PDF fusionnés' },
    'Markdown to PDF':  { icon: 'fa-solid fa-hashtag',      color: '#7C3AED', label: 'Markdown to PDF',  desc: 'Conversion depuis Markdown' },
    'Office to PDF':    { icon: 'fa-solid fa-file-word',    color: '#2563EB', label: 'Office to PDF',    desc: 'Conversion depuis Office' },
    'Screenshot to PDF':{ icon: 'fa-solid fa-camera',       color: '#8B5CF6', label: 'Screenshot PDF',   desc: 'Capture d\'écran en PDF' },
};

function getToolInfo(toolName) {
    return TOOL_INFO[toolName] ?? { icon: 'fa-solid fa-file-pdf', color: '#FF701F', label: toolName ?? 'PDF', desc: 'Génération PDF' };
}

function HistoryRow({ gen, index }) {
    const rowRef = useRef(null);
    const info   = getToolInfo(gen.toolName);

    useEffect(() => {
        gsap.fromTo(rowRef.current,
            { y: 20, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.45, delay: 0.05 * index, ease: 'power3.out' }
        );

        const el = rowRef.current;

        const onEnter = () => gsap.to(el, { x: 6, duration: 0.2, ease: 'power2.out' });
        const onLeave = () => gsap.to(el, { x: 0, duration: 0.2, ease: 'power2.out' });

        el.addEventListener('mouseenter', onEnter);
        el.addEventListener('mouseleave', onLeave);

        return () => {
            el.removeEventListener('mouseenter', onEnter);
            el.removeEventListener('mouseleave', onLeave);
        };
    }, []);

    return (
        <div
            ref={rowRef}
            style={{
                display: 'flex',
                alignItems: 'center',
                gap: '1rem',
                background: '#fff',
                borderRadius: '0.875rem',
                padding: '0.875rem 1.25rem',
                boxShadow: '0 1px 4px rgba(0,0,0,0.06)',
                cursor: 'default',
                opacity: 0,
            }}
        >
            {/* Icône */}
            <div style={{
                width: '3rem', height: '3rem', flexShrink: 0,
                borderRadius: '0.75rem',
                background: `${info.color}18`,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
            }}>
                <i className={info.icon} style={{ color: info.color, fontSize: '1.1rem' }} />
            </div>

            {/* Infos */}
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.2rem' }}>
                    <p style={{ fontWeight: 700, color: '#111827', fontSize: '0.9rem', margin: 0 }}>
                        {info.label}
                    </p>
                    <span style={{
                        fontSize: '0.6rem', fontWeight: 700,
                        background: `${info.color}15`, color: info.color,
                        padding: '0.15rem 0.5rem', borderRadius: '999px',
                    }}>
                        PDF
                    </span>
                </div>
                <p style={{ fontSize: '0.75rem', color: '#9ca3af', margin: 0 }}>
                    {info.desc}
                </p>
            </div>

            {/* Date + heure */}
            <div style={{ textAlign: 'right', flexShrink: 0 }}>
                <p style={{ fontSize: '0.8rem', fontWeight: 600, color: '#374151', margin: 0 }}>
                    {gen.createdAt?.split(' ')[0]}
                </p>
                <p style={{ fontSize: '0.7rem', color: '#9ca3af', margin: 0 }}>
                    {gen.createdAt?.split(' ')[1]}
                </p>
            </div>

            {/* Icône téléchargement */}
            <div style={{
                width: '2rem', height: '2rem', flexShrink: 0,
                borderRadius: '0.5rem',
                background: '#f3f4f6',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
            }}>
                <i className="fa-solid fa-file-arrow-down" style={{ color: '#9ca3af', fontSize: '0.85rem' }} />
            </div>
        </div>
    );
}

export default function Historique({ firstname, lastname, email, generations, generationUsed, generationLimit, planName }) {
    const titleRef   = useRef(null);
    const counterRef = useRef(null);

    useEffect(() => {
        gsap.fromTo(titleRef.current,
            { y: 30, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out' }
        );
        gsap.fromTo(counterRef.current,
            { y: 20, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.6, delay: 0.2, ease: 'power3.out' }
        );
    }, []);

    const isUnlimited = generationLimit === -1 || generationLimit === null;
    const remaining   = isUnlimited ? null : Math.max(0, generationLimit - generationUsed);
    const percent     = isUnlimited ? 100 : Math.min(100, (generationUsed / generationLimit) * 100);
    const barColor    = percent >= 100 ? '#ef4444' : percent >= 80 ? '#f59e0b' : '#22c55e';

    return (
        <div style={{ minHeight: '100vh', display: 'flex', flexDirection: 'column', background: '#7C3AED' }}>
            <Header firstname={firstname} lastname={lastname} email={email} />
            <WhiteBar />

            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '3rem 1.5rem' }}>

                    {/* Titre centré */}
                    <div ref={titleRef} style={{ textAlign: 'center', marginBottom: '2rem', opacity: 0, width: '100%' }}>
                        <h1 style={{ fontFamily: 'Thunder-Extra-Bold, sans-serif', fontSize: 'clamp(40px,20vw,420px)', color: '#fff', lineHeight: 1, margin: '0 0 0.5rem' }}>
                            HISTORIQUE
                        </h1>
                        <p style={{ color: 'rgba(255,255,255,0.65)', fontSize: '0.95rem', margin: 0 }}>
                            Toutes vos générations de PDF
                        </p>
                    </div>

                <div style={{ width: '100%', maxWidth: '680px' }}>

                    {/* Compteur */}
                    <div ref={counterRef} style={{
                        background: 'rgba(255,255,255,0.15)',
                        backdropFilter: 'blur(8px)',
                        borderRadius: '1rem',
                        padding: '1rem 1.5rem',
                        marginBottom: '1.5rem',
                        opacity: 0,
                    }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.5rem' }}>
                            <span style={{ fontSize: '0.8rem', fontWeight: 600, color: 'rgba(255,255,255,0.8)' }}>
                                Plan {planName}
                            </span>
                            <span style={{ fontSize: '0.85rem', fontWeight: 700, color: '#fff' }}>
                                {isUnlimited ? '∞ Illimité' : `${generationUsed} / ${generationLimit}`}
                            </span>
                        </div>
                        {!isUnlimited && (
                            <>
                                <div style={{ background: 'rgba(255,255,255,0.2)', borderRadius: '999px', height: '6px', overflow: 'hidden', marginBottom: '0.4rem' }}>
                                    <div style={{ width: `${percent}%`, height: '100%', background: barColor, borderRadius: '999px' }} />
                                </div>
                                <p style={{ fontSize: '0.75rem', color: 'rgba(255,255,255,0.6)', margin: 0 }}>
                                    {remaining === 0
                                        ? <span style={{ color: '#fca5a5' }}>Limite atteinte — <a href="/pricing" style={{ color: '#fff', fontWeight: 700 }}>Passer à un plan supérieur →</a></span>
                                        : `Il vous reste ${remaining} génération${remaining > 1 ? 's' : ''}`
                                    }
                                </p>
                            </>
                        )}
                    </div>

                    {/* Liste */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.625rem' }}>
                        {generations.length === 0 ? (
                            <div style={{ background: 'rgba(255,255,255,0.12)', borderRadius: '1rem', padding: '3rem', textAlign: 'center' }}>
                                <i className="fa-solid fa-clock-rotate-left" style={{ fontSize: '2.5rem', color: 'rgba(255,255,255,0.3)', marginBottom: '1rem', display: 'block' }} />
                                <p style={{ color: 'rgba(255,255,255,0.6)', margin: '0 0 1.25rem', fontSize: '0.9rem' }}>
                                    Aucune génération pour l'instant.
                                </p>
                                <a href="/convert" style={{
                                    display: 'inline-flex', alignItems: 'center', gap: '0.4rem',
                                    background: '#fff', color: '#FF701F',
                                    fontWeight: 700, fontSize: '0.85rem',
                                    padding: '0.65rem 1.25rem', borderRadius: '0.75rem',
                                    textDecoration: 'none',
                                }}>
                                    <i className="fa-solid fa-plus" />
                                    Générer mon premier PDF
                                </a>
                            </div>
                        ) : (
                            generations.map((gen, i) => (
                                <HistoryRow key={gen.id} gen={gen} index={i} />
                            ))
                        )}
                    </div>

                </div>
            </div>

            <Footer />
        </div>
    );
}
