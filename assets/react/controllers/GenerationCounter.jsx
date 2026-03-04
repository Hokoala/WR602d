import React from 'react';

export default function GenerationCounter({ used, limit, planName }) {
    const isUnlimited = limit === -1 || limit === null;
    const remaining   = isUnlimited ? null : Math.max(0, limit - used);
    const percent     = isUnlimited ? 100 : Math.min(100, (used / limit) * 100);
    const isFull      = !isUnlimited && remaining === 0;

    const barColor = isFull ? '#ef4444' : percent >= 80 ? '#f59e0b' : '#22c55e';

    return (
        <div style={{
            background: 'rgba(255,255,255,0.15)',
            backdropFilter: 'blur(8px)',
            borderRadius: '1rem',
            padding: '1rem 1.5rem',
            color: '#fff',
            minWidth: '260px',
            maxWidth: '340px',
            width: '100%',
        }}>
            {/* Ligne titre */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.5rem' }}>
                <span style={{ fontSize: '0.8rem', fontWeight: 600, opacity: 0.85 }}>
                    Générations — Plan {planName}
                </span>
                <span style={{ fontSize: '0.85rem', fontWeight: 700 }}>
                    {isUnlimited ? (
                        <span style={{ color: '#86efac' }}>Illimité</span>
                    ) : (
                        <span style={{ color: isFull ? '#fca5a5' : '#fff' }}>
                            {used} / {limit}
                        </span>
                    )}
                </span>
            </div>

            {/* Barre de progression */}
            {!isUnlimited && (
                <div style={{ background: 'rgba(255,255,255,0.2)', borderRadius: '999px', height: '6px', overflow: 'hidden', marginBottom: '0.6rem' }}>
                    <div style={{
                        width: `${percent}%`,
                        height: '100%',
                        background: barColor,
                        borderRadius: '999px',
                        transition: 'width 0.4s ease',
                    }} />
                </div>
            )}

            {/* Message */}
            {isFull ? (
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    <span style={{ fontSize: '0.75rem', color: '#fca5a5' }}>
                        Limite atteinte.
                    </span>
                    <a href="/plan" style={{
                        fontSize: '0.75rem',
                        fontWeight: 700,
                        color: '#fff',
                        background: '#ef4444',
                        padding: '0.2rem 0.6rem',
                        borderRadius: '0.4rem',
                        textDecoration: 'none',
                    }}>
                        Passer à un plan supérieur →
                    </a>
                </div>
            ) : !isUnlimited ? (
                <p style={{ fontSize: '0.75rem', opacity: 0.75, margin: 0 }}>
                    Il vous reste <strong style={{ color: '#fff' }}>{remaining}</strong> génération{remaining > 1 ? 's' : ''}.
                </p>
            ) : null}
        </div>
    );
}
