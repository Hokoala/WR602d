import React, { useRef, useEffect } from 'react';
import { gsap } from 'gsap';

export default function Title(props) {
    const titleRef = useRef(null);

    useEffect(() => {
        gsap.fromTo(titleRef.current,
            { y: 80, opacity: 0 },
            { y: 0, opacity: 1, duration: 1, ease: 'power3.out' }
        );
    }, []);

    return (
        <div ref={titleRef} className="font-thunder text-[60px] md:text-[250px] lg:text-[400px] leading-[1] text-white opacity-0">
            {props.Title}
        </div>
    );
}
