import React, { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import Header from './Header';
import WhiteBar from './WhiteBar';

export default function HomePage() {
    const titleRef = useRef(null);
    const btnRef = useRef(null);

    useEffect(() => {
        gsap.fromTo(titleRef.current,
            { y: 80, opacity: 0 },
            { y: 0, opacity: 1, duration: 1, ease: 'power3.out' }
        );
        gsap.fromTo(btnRef.current,
            { scale: 0, opacity: 0 },
            { scale: 1, opacity: 1, duration: 0.8, delay: 0.6, ease: 'back.out(1.7)' }
        );
    }, []);

    return (
        <div style={{ width: '100vw', height: '100vh', background: '#FF701F', display: 'flex', flexDirection: 'column' }}>
            <Header />
            <WhiteBar />

            <div className="flex-1 flex flex-col items-center justify-start pt-4">
                <div ref={titleRef} className="font-thunder text-[60px] md:text-[250px] lg:text-[400px] leading-[1] text-white" style={{ opacity: 0 }}>
                    PDF EN UN CLIC
                </div>
                <div ref={btnRef} className="mt-10" style={{ opacity: 0 }}>
                    <a
                        href="/generate-pdf"
                        className="w-32 h-32 md:w-48 md:h-48 bg-white rounded-full flex items-center justify-center shadow-lg hover:scale-105 transition-transform cursor-pointer"
                    >
                        <span className="font-thunder text-[20px] md:text-[30px] text-[#FF701F] text-center leading-[1]">
                            GENERER PDF
                        </span>
                    </a>
                </div>
            </div>
        </div>
    );
}
