import React, { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export default function ScrollSections() {
    const s1 = useRef(null);
    const s2 = useRef(null);
    const s3 = useRef(null);

    useEffect(() => {
        const timer = setTimeout(() => {
            // Section 1 scale down quand on scroll
            gsap.to(s1.current, {
                scale: 0.85,
                opacity: 0.3,
                scrollTrigger: {
                    trigger: s1.current,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true,
                },
            });

            // Section 2 scale down sans fade
            gsap.to(s2.current, {
                scale: 0.85,
                scrollTrigger: {
                    trigger: s2.current,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true,
                },
            });
        }, 100);

        return () => {
            clearTimeout(timer);
            ScrollTrigger.getAll().forEach((t) => t.kill());
        };
    }, []);

    const stickyStyle = {
        position: 'sticky',
        top: 0,
        width: '100vw',
        height: '100vh',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
    };

    return (
        <div>
            <section ref={s1} style={{ ...stickyStyle, background: '#7F9AAE', zIndex: 1, flexDirection: 'column', padding: '0 5%' }}>
                <h2 className="font-thunder text-[80px] md:text-[150px] lg:text-[200px] leading-[1] text-white uppercase mb-8">
                    Comment ça marche ?
                </h2>
                <div className="flex flex-col md:flex-row gap-8 md:gap-16 max-w-5xl">
                    <div className="flex-1 border-l-4 border-white/30 pl-6">
                        <span className="font-thunder text-[40px] md:text-[60px] text-white/30">01</span>
                        <h3 className="text-xl md:text-2xl text-white font-bold mt-2">Choisissez votre fichier</h3>
                        <p className="text-white/70 mt-2">URL ou fichier HTML, à vous de choisir.</p>
                    </div>
                    <div className="flex-1 border-l-4 border-white/30 pl-6">
                        <span className="font-thunder text-[40px] md:text-[60px] text-white/30">02</span>
                        <h3 className="text-xl md:text-2xl text-white font-bold mt-2">Convertissez en un clic</h3>
                        <p className="text-white/70 mt-2">Simple, rapide et sécurisé.</p>
                    </div>
                    <div className="flex-1 border-l-4 border-white/30 pl-6">
                        <span className="font-thunder text-[40px] md:text-[60px] text-white/30">03</span>
                        <h3 className="text-xl md:text-2xl text-white font-bold mt-2">Téléchargez votre PDF</h3>
                        <p className="text-white/70 mt-2">Vos fichiers sont supprimés après 24h.</p>
                    </div>
                </div>
            </section>

            <section ref={s2} style={{ ...stickyStyle, background: '#CF909D', zIndex: 2, flexDirection: 'column', padding: '0 5%' }}>
                <h2 className="font-thunder text-[80px] md:text-[150px] lg:text-[200px] leading-[1] text-white uppercase mb-10">
                    Nos offres
                </h2>
                <div className="flex flex-col md:flex-row gap-6 max-w-5xl w-full">
                    <div className="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-8 flex flex-col justify-between border border-white/20">
                        <div>
                            <h3 className="font-thunder text-[40px] md:text-[50px] text-white uppercase">Free</h3>
                            <p className="font-thunder text-[50px] md:text-[70px] text-white leading-[1] mt-2">0€</p>
                            <ul className="text-white/80 mt-6 space-y-3 text-sm">
                                <li>5 conversions / jour</li>
                                <li>Fichiers jusqu'à 5 Mo</li>
                                <li>Support communautaire</li>
                            </ul>
                        </div>
                        <a href="/register" className="mt-8 block text-center bg-white/20 hover:bg-white/30 text-white py-3 rounded-lg transition-all">Commencer</a>
                    </div>

                    <div className="flex-1 bg-white rounded-lg p-8 flex flex-col justify-between border-4 border-white transform md:scale-105">
                        <div>
                            <span className="text-[10px] font-mono uppercase tracking-widest bg-[#FF701F] text-white px-3 py-1 rounded-full">Populaire</span>
                            <h3 className="font-thunder text-[40px] md:text-[50px] text-[#FF701F] uppercase mt-4">Basic</h3>
                            <p className="font-thunder text-[50px] md:text-[70px] text-[#FF701F] leading-[1] mt-2">9€<span className="text-[20px] text-black/40">/mois</span></p>
                            <ul className="text-black/70 mt-6 space-y-3 text-sm">
                                <li>50 conversions / jour</li>
                                <li>Fichiers jusqu'à 50 Mo</li>
                                <li>Support prioritaire</li>
                                <li>Conversion par lot</li>
                            </ul>
                        </div>
                        <a href="/register" className="mt-8 block text-center bg-[#FF701F] hover:bg-[#e5631a] text-white py-3 rounded-lg transition-all font-bold">Choisir Basic</a>
                    </div>

                    <div className="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-8 flex flex-col justify-between border border-white/20">
                        <div>
                            <h3 className="font-thunder text-[40px] md:text-[50px] text-white uppercase">Premium</h3>
                            <p className="font-thunder text-[50px] md:text-[70px] text-white leading-[1] mt-2">29€<span className="text-[20px] text-white/40">/mois</span></p>
                            <ul className="text-white/80 mt-6 space-y-3 text-sm">
                                <li>Conversions illimitées</li>
                                <li>Fichiers jusqu'à 200 Mo</li>
                                <li>Support dédié 24/7</li>
                                <li>API access</li>
                            </ul>
                        </div>
                        <a href="/register" className="mt-8 block text-center bg-white/20 hover:bg-white/30 text-white py-3 rounded-lg transition-all">Choisir Premium</a>
                    </div>
                </div>
            </section>

            <section ref={s3} style={{ ...stickyStyle, background: '#111', zIndex: 3, flexDirection: 'column', padding: '0 5%' }}>
                <h2 className="font-thunder text-[80px] md:text-[150px] lg:text-[200px] leading-[1] text-[#FF701F] uppercase mb-8">
                    À propos
                </h2>
                <div className="max-w-3xl text-center">
                    <p className="text-white/70 text-lg md:text-xl leading-relaxed mb-8">
                        Nous sommes une équipe passionnée qui croit que la conversion de documents devrait être accessible à tous. Notre technologie propulsée par Gotenberg garantit des PDF de qualité professionnelle, à chaque fois.
                    </p>
                    <div className="flex flex-col md:flex-row gap-8 justify-center mt-10">
                        <div className="text-center">
                            <p className="font-thunder text-[60px] md:text-[80px] text-[#FF701F] leading-[1]">10K+</p>
                            <p className="text-white/50 text-sm mt-2">Utilisateurs</p>
                        </div>
                        <div className="text-center">
                            <p className="font-thunder text-[60px] md:text-[80px] text-[#FF701F] leading-[1]">500K+</p>
                            <p className="text-white/50 text-sm mt-2">PDF générés</p>
                        </div>
                        <div className="text-center">
                            <p className="font-thunder text-[60px] md:text-[80px] text-[#FF701F] leading-[1]">99.9%</p>
                            <p className="text-white/50 text-sm mt-2">Uptime</p>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    );
}
