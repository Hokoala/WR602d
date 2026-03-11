import React, { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import Header from './Header';
import WhiteBar from './WhiteBar';
import Footer from './Footer';

export default function PaymentSuccess({ firstname, lastname, email }) {
    const cardRef = useRef(null);

    useEffect(() => {
        gsap.fromTo(cardRef.current,
            { y: 40, opacity: 0, scale: 0.95 },
            { y: 0, opacity: 1, scale: 1, duration: 0.7, ease: 'power3.out' }
        );
    }, []);

    return (
        <div style={{ minHeight: '100vh', display: 'flex', flexDirection: 'column', background: '#0f172a' }}>
            <Header firstname={firstname} lastname={lastname} email={email} />
            <WhiteBar />

            <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '3rem 1.5rem' }}>
                <div ref={cardRef} style={{
                    background: '#fff',
                    borderRadius: '1.5rem',
                    padding: '3rem 2.5rem',
                    textAlign: 'center',
                    maxWidth: '440px',
                    width: '100%',
                    boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
                    opacity: 0,
                }}>
                    <div style={{
                        width: '4rem', height: '4rem',
                        background: '#dcfce7',
                        borderRadius: '9999px',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        margin: '0 auto 1.5rem',
                    }}>
                        <i className="fa-solid fa-check" style={{ color: '#22c55e', fontSize: '1.5rem' }} />
                    </div>

                    <h1 style={{ fontFamily: 'Thunder-Extra-Bold, sans-serif', fontSize: '2.5rem', color: '#111827', lineHeight: 1, margin: '0 0 0.75rem' }}>
                        PAIEMENT RÉUSSI !
                    </h1>
                    <p style={{ color: '#9ca3af', fontSize: '0.9rem', marginBottom: '2rem', lineHeight: 1.6 }}>
                        Votre abonnement est en cours d'activation.<br />
                        Vous recevrez une confirmation par email.
                    </p>

                    <a href="/home" style={{
                        display: 'block',
                        background: '#FF701F',
                        color: '#fff',
                        fontWeight: 700,
                        padding: '0.875rem',
                        borderRadius: '0.875rem',
                        textDecoration: 'none',
                        fontSize: '0.9rem',
                    }}>
                        Retour à l'accueil
                    </a>
                </div>
            </div>

            <Footer />
        </div>
    );
}
