import React, { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const UrlIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="w-8 h-8 md:w-10 md:h-10 text-white">
        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
    </svg>
);

const HtmlIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="w-8 h-8 md:w-10 md:h-10 text-white">
        <polyline points="16 18 22 12 16 6" />
        <polyline points="8 6 2 12 8 18" />
    </svg>
);

const MergeIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="w-8 h-8 md:w-10 md:h-10 text-white">
        <rect x="2" y="3" width="8" height="8" rx="1" />
        <rect x="14" y="3" width="8" height="8" rx="1" />
        <rect x="8" y="13" width="8" height="8" rx="1" />
        <path d="M6 11v2a2 2 0 0 0 2 2h0" />
        <path d="M18 11v2a2 2 0 0 1-2 2h0" />
    </svg>
);

const SplitIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="w-8 h-8 md:w-10 md:h-10 text-white">
        <line x1="12" y1="2" x2="12" y2="22" strokeDasharray="4 4" />
        <rect x="2" y="4" width="8" height="16" rx="1" />
        <rect x="14" y="4" width="8" height="16" rx="1" />
    </svg>
);

const CompressIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="w-8 h-8 md:w-10 md:h-10 text-white">
        <polyline points="4 14 10 14 10 20" />
        <polyline points="20 10 14 10 14 4" />
        <line x1="14" y1="10" x2="21" y2="3" />
        <line x1="3" y1="21" x2="10" y2="14" />
    </svg>
);

const tools = [
    {
        name: 'URL to PDF',
        icon: UrlIcon,
        description: 'Convertir une URL en fichier PDF.',
        color: '#FF701F',
        link: '/generate-pdf',
    },
    {
        name: 'HTML to PDF',
        icon: HtmlIcon,
        description: 'Convertir du code HTML en fichier PDF.',
        color: '#3B82F6',
        link: '/html-to-pdf',
    },
    {
        name: 'Merge PDF',
        icon: MergeIcon,
        description: 'Fusionner plusieurs fichiers PDF en un seul.',
        color: '#10B981',
        link: '/generate-pdf',
    },
    {
        name: 'Split PDF',
        icon: SplitIcon,
        description: 'Diviser un fichier PDF en plusieurs pages.',
        color: '#8B5CF6',
        link: '/generate-pdf',
    },
    {
        name: 'Compress PDF',
        icon: CompressIcon,
        description: 'Compresser un fichier PDF pour réduire sa taille.',
        color: '#F59E0B',
        link: '/generate-pdf',
    },
];

export default function ScrollSections() {
    const s1 = useRef(null);
    const sTools = useRef(null);
    const s2 = useRef(null);
    const s3 = useRef(null);
    const toolCardsRef = useRef([]);

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

            // Section Tools scale down
            gsap.to(sTools.current, {
                scale: 0.85,
                opacity: 0.3,
                scrollTrigger: {
                    trigger: sTools.current,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true,
                },
            });

            // Animate tool cards on scroll
            toolCardsRef.current.forEach((card, i) => {
                if (!card) return;
                gsap.fromTo(card,
                    { y: 60, opacity: 0, scale: 0.9 },
                    {
                        y: 0,
                        opacity: 1,
                        scale: 1,
                        duration: 0.6,
                        ease: 'power3.out',
                        scrollTrigger: {
                            trigger: sTools.current,
                            start: 'top 80%',
                            toggleActions: 'play none none reverse',
                        },
                        delay: i * 0.1,
                    }
                );
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

            {/* Section Nos Outils */}
            <section ref={sTools} style={{ ...stickyStyle, background: '#1E293B', zIndex: 2, flexDirection: 'column', padding: '0 5%' }}>
                <h2 className="font-thunder text-[80px] md:text-[150px] lg:text-[200px] leading-[1] text-white uppercase mb-10">
                    Nos Outils
                </h2>
                <div className="flex flex-wrap justify-center gap-6 max-w-6xl w-full">
                    {tools.map((tool, index) => {
                        const IconComponent = tool.icon;
                        return (
                            <a
                                key={tool.name}
                                href={tool.link}
                                ref={(el) => (toolCardsRef.current[index] = el)}
                                className="group relative w-[160px] md:w-[200px] bg-white/5 backdrop-blur-sm rounded-2xl p-6 flex flex-col items-center text-center border border-white/10 hover:border-white/30 transition-all duration-300 hover:-translate-y-2 no-underline"
                                style={{ opacity: 0 }}
                            >
                                <div
                                    className="w-16 h-16 md:w-20 md:h-20 rounded-full flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110"
                                    style={{ background: tool.color }}
                                >
                                    <IconComponent />
                                </div>
                                <h3 className="text-white font-bold text-sm md:text-base leading-tight mb-2">
                                    {tool.name}
                                </h3>
                                <p className="text-white/50 text-xs md:text-sm leading-snug">
                                    {tool.description}
                                </p>
                                <div
                                    className="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-300"
                                    style={{ background: tool.color }}
                                />
                            </a>
                        );
                    })}
                </div>
            </section>

            <section ref={s2} style={{ ...stickyStyle, background: '#CF909D', zIndex: 3, flexDirection: 'column', padding: '0 5%' }}>
                <h2 className="font-thunder text-[80px] md:text-[150px] lg:text-[200px] leading-[1] text-white uppercase mb-10">
                    Nos offres
                </h2>
                <div className="flex flex-col md:flex-row gap-6 max-w-5xl w-full">
                    <div className="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-8 flex flex-col justify-between border border-white/20">
                        <div>
                            <h3 className="font-thunder text-[40px] md:text-[50px] text-white uppercase">Free</h3>
                            <p className="font-thunder text-[50px] md:text-[70px] text-white leading-[1] mt-2">0€</p>
                            <ul className="mt-6 space-y-3 text-sm">
                                {['5 conversions / jour', 'Fichiers jusqu\'à 5 Mo', 'Support communautaire'].map(f => (
                                    <li key={f} className="flex items-center gap-2 text-white/80">
                                        <svg xmlns="http://www.w3.org/2000/svg" style={{width:'1rem',height:'1rem',flexShrink:0,color:'rgba(255,255,255,0.5)'}} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {f}
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <a href="/plan" className="mt-8 block text-center bg-white/20 hover:bg-white/30 text-white py-3 rounded-lg transition-all">Commencer</a>
                    </div>

                    <div className="flex-1 bg-white rounded-lg p-8 flex flex-col justify-between border-4 border-white transform md:scale-105">
                        <div>
                            <span className="text-[10px] font-mono uppercase tracking-widest bg-[#FF701F] text-white px-3 py-1 rounded-full">Populaire</span>
                            <h3 className="font-thunder text-[40px] md:text-[50px] text-[#FF701F] uppercase mt-4">Basic</h3>
                            <p className="font-thunder text-[50px] md:text-[70px] text-[#FF701F] leading-[1] mt-2">9€<span className="text-[20px] text-black/40">/mois</span></p>
                            <ul className="mt-6 space-y-3 text-sm">
                                {['50 conversions / jour', 'Fichiers jusqu\'à 50 Mo', 'Support prioritaire', 'Conversion par lot'].map(f => (
                                    <li key={f} className="flex items-center gap-2 text-black/70">
                                        <svg xmlns="http://www.w3.org/2000/svg" style={{width:'1rem',height:'1rem',flexShrink:0,color:'#FF701F'}} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {f}
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <a href="/plan" className="mt-8 block text-center bg-[#FF701F] hover:bg-[#e5631a] text-white py-3 rounded-lg transition-all font-bold">Choisir Basic</a>
                    </div>

                    <div className="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-8 flex flex-col justify-between border border-white/20">
                        <div>
                            <h3 className="font-thunder text-[40px] md:text-[50px] text-white uppercase">Premium</h3>
                            <p className="font-thunder text-[50px] md:text-[70px] text-white leading-[1] mt-2">29€<span className="text-[20px] text-white/40">/mois</span></p>
                            <ul className="mt-6 space-y-3 text-sm">
                                {['Conversions illimitées', 'Fichiers jusqu\'à 200 Mo', 'Support dédié 24/7', 'API access'].map(f => (
                                    <li key={f} className="flex items-center gap-2 text-white/80">
                                        <svg xmlns="http://www.w3.org/2000/svg" style={{width:'1rem',height:'1rem',flexShrink:0,color:'rgba(255,255,255,0.6)'}} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {f}
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <a href="/plan" className="mt-8 block text-center bg-white/20 hover:bg-white/30 text-white py-3 rounded-lg transition-all">Choisir Premium</a>
                    </div>
                </div>
            </section>

            <section ref={s3} style={{ ...stickyStyle, background: '#111', zIndex: 4, flexDirection: 'column', padding: '0 5%' }}>
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
