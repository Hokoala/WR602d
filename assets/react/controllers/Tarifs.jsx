import React, { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import Header from './Header';
import WhiteBar from './WhiteBar';
import Footer from './Footer';

const CheckIcon = ({ color }) => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        style={{ width: '1rem', height: '1rem', flexShrink: 0, color }}
        fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5"
    >
        <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
    </svg>
);

function PricingCard({ plan, isPopular, isCurrent, index }) {
    const cardRef = useRef(null);

    const isFree      = parseFloat(plan.price) === 0;
    const isUnlimited = plan.limitGeneration === -1 || plan.limitGeneration === null;
    const hasSpecial  = plan.specialPrice !== null;
    const displayPrice = hasSpecial ? parseFloat(plan.specialPrice) : parseFloat(plan.price);

    const priceLabel = isFree
        ? '0€'
        : `${displayPrice % 1 === 0 ? displayPrice : displayPrice.toFixed(2)}€`;

    useEffect(() => {
        gsap.fromTo(cardRef.current,
            { y: 40, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.6, delay: 0.12 * index, ease: 'power3.out' }
        );
    }, []);

    if (isPopular) {
        return (
            <div
                ref={cardRef}
                className="flex-1 bg-white rounded-lg p-8 flex flex-col justify-between border-4 border-white md:scale-105"
                style={{ opacity: 0, position: 'relative', minWidth: '260px' }}
            >
                {isCurrent && (
                    <span className="absolute -top-4 left-1/2 -translate-x-1/2 bg-[#FF701F] text-white text-[10px] font-mono uppercase tracking-widest px-3 py-1 rounded-full whitespace-nowrap">
                        Votre plan actuel
                    </span>
                )}
                <div>
                    <span className="text-[10px] font-mono uppercase tracking-widest bg-[#FF701F] text-white px-3 py-1 rounded-full">
                        Populaire
                    </span>
                    <h3 className="font-thunder text-[40px] md:text-[50px] text-[#FF701F] uppercase mt-4">
                        {plan.name}
                    </h3>
                    <p className="font-thunder text-[50px] md:text-[70px] text-[#FF701F] leading-[1] mt-2">
                        {priceLabel}
                        {!isFree && (
                            <span className="text-[20px] text-black/40">/mois</span>
                        )}
                        {hasSpecial && (
                            <span className="text-[18px] text-black/30 line-through ml-3">
                                {parseFloat(plan.price)}€
                            </span>
                        )}
                    </p>
                    <p className="text-black/50 text-sm mt-3 leading-snug">{plan.description}</p>
                    <ul className="mt-6 space-y-3 text-sm">
                        <li className="flex items-center gap-2 text-black/70">
                            <CheckIcon color="#FF701F" />
                            {isUnlimited ? 'Générations illimitées' : `${plan.limitGeneration} générations / mois`}
                        </li>
                        {plan.tools.map((tool) => (
                            <li key={tool} className="flex items-center gap-2 text-black/70">
                                <CheckIcon color="#FF701F" />
                                {tool}
                            </li>
                        ))}
                    </ul>
                </div>
                <a
                    href={isCurrent ? '/generate-pdf' : '/register'}
                    className="mt-8 block text-center bg-[#FF701F] hover:bg-[#e5631a] text-white py-3 rounded-lg transition-all font-bold"
                >
                    {isCurrent ? 'Utiliser mes outils →' : `Choisir ${plan.name}`}
                </a>
            </div>
        );
    }

    return (
        <div
            ref={cardRef}
            className="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-8 flex flex-col justify-between border border-white/20"
            style={{ opacity: 0, position: 'relative', minWidth: '260px' }}
        >
            {isCurrent && (
                <span className="absolute -top-4 left-1/2 -translate-x-1/2 bg-white text-[#CF909D] text-[10px] font-mono uppercase tracking-widest px-3 py-1 rounded-full whitespace-nowrap">
                    Votre plan actuel
                </span>
            )}
            <div>
                <h3 className="font-thunder text-[40px] md:text-[50px] text-white uppercase">
                    {plan.name}
                </h3>
                <p className="font-thunder text-[50px] md:text-[70px] text-white leading-[1] mt-2">
                    {priceLabel}
                    {!isFree && (
                        <span className="text-[20px] text-white/40">/mois</span>
                    )}
                    {hasSpecial && (
                        <span className="text-[18px] text-white/30 line-through ml-3">
                            {parseFloat(plan.price)}€
                        </span>
                    )}
                </p>
                <p className="text-white/60 text-sm mt-3 leading-snug">{plan.description}</p>
                <ul className="mt-6 space-y-3 text-sm">
                    <li className="flex items-center gap-2 text-white/80">
                        <CheckIcon color="rgba(255,255,255,0.5)" />
                        {isUnlimited ? 'Générations illimitées' : `${plan.limitGeneration} générations / mois`}
                    </li>
                    {plan.tools.map((tool) => (
                        <li key={tool} className="flex items-center gap-2 text-white/80">
                            <CheckIcon color="rgba(255,255,255,0.5)" />
                            {tool}
                        </li>
                    ))}
                </ul>
            </div>
            <a
                href={isCurrent ? '/generate-pdf' : '/register'}
                className="mt-8 block text-center bg-white/20 hover:bg-white/30 text-white py-3 rounded-lg transition-all"
            >
                {isCurrent ? 'Utiliser mes outils →' : isFree ? 'Commencer' : `Choisir ${plan.name}`}
            </a>
        </div>
    );
}

export default function Tarifs({ firstname, lastname, email, plans, currentPlanId }) {
    const titleRef = useRef(null);
    const cardsRef = useRef(null);

    useEffect(() => {
        gsap.fromTo(titleRef.current,
            { y: 50, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out' }
        );
        gsap.fromTo(cardsRef.current,
            { y: 30, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.5, delay: 0.3, ease: 'power3.out' }
        );
    }, []);

    // Le plan du milieu est "populaire"
    const middleIndex = Math.floor(plans.length / 2);

    return (
        <div style={{ minHeight: '100vh', display: 'flex', flexDirection: 'column', background: '#0f172a' }}>
            <Header firstname={firstname} lastname={lastname} email={email} />
            <WhiteBar />

            <div className="flex flex-col items-center justify-start flex-1 px-[5%] py-12">
                <h1
                    ref={titleRef}
                    className="font-thunder text-[80px] md:text-[150px] lg:text-[200px] leading-[1] text-white uppercase mb-10 text-center"
                    style={{ opacity: 0 }}
                >
                    Nos offres
                </h1>

                <div
                    ref={cardsRef}
                    className="flex flex-col md:flex-row gap-6 max-w-5xl w-full"
                    style={{ opacity: 0 }}
                >
                    {plans.map((plan, i) => (
                        <PricingCard
                            key={plan.id}
                            plan={plan}
                            isPopular={i === middleIndex}
                            isCurrent={plan.id === currentPlanId}
                            index={i}
                        />
                    ))}
                </div>

                <p className="mt-10 text-white/40 text-xs text-center">
                    Tous les prix sont TTC · Annulation possible à tout moment
                </p>
            </div>

            <Footer />
        </div>
    );
}
