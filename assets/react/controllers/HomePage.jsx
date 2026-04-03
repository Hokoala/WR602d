import React, { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import Header from './Header';
import WhiteBar from './WhiteBar';

export default function HomePage({ firstname, lastname, email }) {
    const titleRef  = useRef(null);
    const btnRef    = useRef(null);
    const aura1Ref  = useRef(null);
    const aura2Ref  = useRef(null);
    const aura3Ref  = useRef(null);
    const shimmerRef = useRef(null);
    const ringRef   = useRef(null);
    const textRef   = useRef(null);

    useEffect(() => {
        // Entrée du titre
        gsap.fromTo(titleRef.current,
            { y: 80, opacity: 0 },
            { y: 0, opacity: 1, duration: 1, ease: 'power3.out' }
        );

        // Entrée du bouton
        gsap.fromTo(btnRef.current,
            { scale: 0, opacity: 0 },
            {
                scale: 1, opacity: 1, duration: 0.8, delay: 0.6, ease: 'back.out(1.7)',
                onComplete: () => {
                    // Respiration du bouton
                    gsap.to(btnRef.current, {
                        scale: 1.07,
                        duration: 1.8,
                        ease: 'sine.inOut',
                        yoyo: true,
                        repeat: -1,
                    });
                }
            }
        );

        // Auras extérieures
        gsap.fromTo(aura1Ref.current,
            { scale: 1, opacity: 0.5 },
            { scale: 1.35, opacity: 0, duration: 1.6, ease: 'power1.out', repeat: -1, delay: 0.8 }
        );
        gsap.fromTo(aura2Ref.current,
            { scale: 1, opacity: 0.35 },
            { scale: 1.65, opacity: 0, duration: 2.2, ease: 'power1.out', repeat: -1, delay: 1.3 }
        );
        gsap.fromTo(aura3Ref.current,
            { scale: 1, opacity: 0.2 },
            { scale: 2, opacity: 0, duration: 3, ease: 'power1.out', repeat: -1, delay: 1.8 }
        );

        // Brillance qui tourne en boucle dans le bouton
        gsap.to(shimmerRef.current, {
            rotate: 360,
            duration: 3,
            ease: 'none',
            repeat: -1,
            delay: 1.4,
        });

        // Halo intérieur orange qui pulse
        gsap.to(ringRef.current, {
            opacity: 0.18,
            scale: 0.88,
            duration: 1.6,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: 1.4,
        });

        // Texte qui pulse légèrement
        gsap.to(textRef.current, {
            opacity: 0.65,
            duration: 1.8,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: 1.4,
        });

    }, []);

    const btnSize = 'clamp(100px, 15vw, 200px)';

    return (
        <div style={{ width: '100vw', height: '100vh', background: '#FF701F', display: 'flex', flexDirection: 'column' }}>
            <Header firstname={firstname} lastname={lastname} email={email} />
            <WhiteBar />

            <div className="flex-1 flex flex-col items-center justify-start pt-4">
                <div ref={titleRef} className="font-thunder leading-[1] text-white text-center px-2" style={{ fontSize: 'clamp(40px, 20vw, 420px)', opacity: 0 }}>
                    PDF EN UN CLIC
                </div>

                <div className="mt-10" style={{ position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'center', width: btnSize, height: btnSize }}>

                    {/* Auras extérieures */}
                    <div ref={aura1Ref} style={{ position: 'absolute', inset: 0, borderRadius: '9999px', background: 'rgba(255,255,255,0.4)', pointerEvents: 'none' }} />
                    <div ref={aura2Ref} style={{ position: 'absolute', inset: 0, borderRadius: '9999px', background: 'rgba(255,255,255,0.25)', pointerEvents: 'none' }} />
                    <div ref={aura3Ref} style={{ position: 'absolute', inset: 0, borderRadius: '9999px', background: 'rgba(255,255,255,0.15)', pointerEvents: 'none' }} />

                    {/* Bouton blanc */}
                    <a
                        ref={btnRef}
                        href="/convert"
                        style={{
                            position: 'relative', zIndex: 10,
                            width: btnSize, height: btnSize,
                            background: '#fff',
                            borderRadius: '9999px',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            boxShadow: '0 8px 40px rgba(0,0,0,0.18)',
                            textDecoration: 'none',
                            opacity: 0,
                            overflow: 'hidden',
                        }}
                    >
                        {/* Halo intérieur orange */}
                        <div ref={ringRef} style={{
                            position: 'absolute', inset: 0,
                            borderRadius: '9999px',
                            background: 'radial-gradient(circle, rgba(255,112,31,0.35) 0%, transparent 70%)',
                            pointerEvents: 'none',
                            opacity: 0.08,
                        }} />

                        {/* Brillance tournante */}
                        <div ref={shimmerRef} style={{
                            position: 'absolute', inset: '-50%',
                            background: 'conic-gradient(from 0deg, transparent 0%, rgba(255,255,255,0.55) 15%, transparent 30%)',
                            pointerEvents: 'none',
                            borderRadius: '9999px',
                        }} />

                        {/* Texte */}
                        <span ref={textRef} className="font-thunder text-[#FF701F] text-center leading-[1]"
                            style={{ fontSize: 'clamp(14px, 2.5vw, 32px)', position: 'relative', zIndex: 2 }}>
                            GENERER PDF
                        </span>
                    </a>
                </div>
            </div>
        </div>
    );
}
