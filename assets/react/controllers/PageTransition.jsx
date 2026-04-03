import React, { useRef, useEffect, useCallback } from 'react';
import { gsap } from 'gsap';

const numPoints = 10;
const numPaths = 2;
const delayPointsMax = 0.3;
const delayPerPath = 0.25;

export default function PageTransition() {
    const svgRef = useRef(null);
    const pathsRef = useRef([]);
    const allPointsRef = useRef([]);
    const pointsDelayRef = useRef([]);
    const tlRef = useRef(null);

    const render = useCallback(() => {
        const allPoints = allPointsRef.current;

        for (let i = 0; i < numPaths; i++) {
            const path = pathsRef.current[i];
            const points = allPoints[i];
            if (!path || !points) continue;

            let d = `M 0 0 V ${points[0]} C`;

            for (let j = 0; j < numPoints - 1; j++) {
                const p = ((j + 1) / (numPoints - 1)) * 100;
                const cp = p - (1 / (numPoints - 1) * 100) / 2;
                d += ` ${cp} ${points[j]} ${cp} ${points[j + 1]} ${p} ${points[j + 1]}`;
            }

            d += ' V 0 H 0';
            path.setAttribute('d', d);
        }
    }, []);

    useEffect(() => {
        const allPoints = [];
        for (let i = 0; i < numPaths; i++) {
            const points = [];
            for (let j = 0; j < numPoints; j++) {
                points.push(100);
            }
            allPoints.push(points);
        }
        allPointsRef.current = allPoints;

        tlRef.current = gsap.timeline({
            onUpdate: render,
            onComplete: () => {
                if (svgRef.current) {
                    svgRef.current.style.pointerEvents = 'none';
                }
            }
        });

        for (let i = 0; i < numPoints; i++) {
            pointsDelayRef.current[i] = Math.random() * delayPointsMax;
        }

        for (let i = 0; i < numPaths; i++) {
            const points = allPoints[i];
            const pathDelay = delayPerPath * (numPaths - i - 1);

            for (let j = 0; j < numPoints; j++) {
                const delay = pointsDelayRef.current[j];
                tlRef.current.to(points, { [j]: 0, duration: 0.9, ease: 'power2.inOut' }, delay + pathDelay);
            }
        }
    }, [render]);

    return (
        <svg
            ref={svgRef}
            className="fixed top-0 left-0 w-full h-full z-50"
            viewBox="0 0 100 100"
            preserveAspectRatio="none"
        >
            <defs>
                <linearGradient id="gradient1" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stopColor="#FF4500" />
                    <stop offset="100%" stopColor="#FF701F" />
                </linearGradient>
                <linearGradient id="gradient2" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stopColor="#FF701F" />
                    <stop offset="100%" stopColor="#FFB347" />
                </linearGradient>
            </defs>
            <path ref={(el) => (pathsRef.current[0] = el)} fill="url(#gradient2)" />
            <path ref={(el) => (pathsRef.current[1] = el)} fill="url(#gradient1)" />
        </svg>
    );
}
