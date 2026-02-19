import React, { useRef, useEffect } from 'react';
import { gsap } from 'gsap';

export default function PdfButton() {
    const btnRef = useRef(null);

    useEffect(() => {
        gsap.fromTo(btnRef.current,
            { scale: 0, opacity: 0 },
            { scale: 1, opacity: 1, duration: 0.8, delay: 0.6, ease: 'back.out(1.7)' }
        );
    }, []);

    return (
        <div className="flex justify-center mt-10">
            <a
                ref={btnRef}
                href="/generate-pdf"
                className="w-32 h-32 md:w-48 md:h-48 bg-white rounded-full flex items-center justify-center shadow-lg hover:scale-105 transition-transform cursor-pointer opacity-0"
            >
                <span className="font-thunder text-[20px] md:text-[30px] text-[#FF701F] text-center leading-[1]">
                    GENERER PDF
                </span>
            </a>
        </div>
    );
}
