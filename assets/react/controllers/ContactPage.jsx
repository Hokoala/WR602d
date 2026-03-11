import React, { useEffect, useRef, useState } from 'react';
import { gsap } from 'gsap';
import Header from './Header';
import WhiteBar from './WhiteBar';
import Footer from './Footer';

export default function ContactPage({ firstname, lastname, email, csrfToken }) {
    const titleRef = useRef(null);
    const formRef = useRef(null);

    const [form, setForm] = useState({
        name: [firstname, lastname].filter(Boolean).join(' ') || '',
        email: email || '',
        subject: '',
        message: '',
    });
    const [status, setStatus] = useState(null); // null | 'sending' | 'success' | 'error'
    const [errors, setErrors] = useState({});

    useEffect(() => {
        gsap.fromTo(titleRef.current,
            { y: 50, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out' }
        );
        gsap.fromTo(formRef.current,
            { y: 30, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.6, delay: 0.3, ease: 'power3.out' }
        );
    }, []);

    const validate = () => {
        const e = {};
        if (!form.name.trim()) e.name = 'Le nom est requis.';
        if (!form.email.trim()) e.email = "L'email est requis.";
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) e.email = 'Email invalide.';
        if (!form.subject.trim()) e.subject = 'Le sujet est requis.';
        if (!form.message.trim()) e.message = 'Le message est requis.';
        return e;
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm(prev => ({ ...prev, [name]: value }));
        if (errors[name]) setErrors(prev => ({ ...prev, [name]: null }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const validation = validate();
        if (Object.keys(validation).length > 0) {
            setErrors(validation);
            return;
        }
        setStatus('sending');
        try {
            const res = await fetch('/contact/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...form, _token: csrfToken }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                setStatus('success');
                setForm(prev => ({ ...prev, subject: '', message: '' }));
            } else {
                setStatus('error');
            }
        } catch {
            setStatus('error');
        }
    };

    const inputClass = "w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/40 text-sm focus:outline-none focus:border-[#FF701F] transition-colors";
    const errorClass = "text-red-400 text-xs mt-1";

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
                    Contact
                </h1>

                <div
                    ref={formRef}
                    className="w-full max-w-2xl"
                    style={{ opacity: 0 }}
                >
                    {status === 'success' ? (
                        <div className="bg-green-500/20 border border-green-500/40 rounded-lg p-8 text-center">
                            <p className="text-green-400 font-bold text-lg mb-2">Message envoyé !</p>
                            <p className="text-white/60 text-sm">Nous vous répondrons dans les plus brefs délais.</p>
                            <button
                                onClick={() => setStatus(null)}
                                className="mt-6 text-[#FF701F] text-sm hover:underline"
                            >
                                Envoyer un autre message
                            </button>
                        </div>
                    ) : (
                        <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <input
                                        type="text"
                                        name="name"
                                        value={form.name}
                                        onChange={handleChange}
                                        placeholder="Votre nom"
                                        className={inputClass}
                                    />
                                    {errors.name && <p className={errorClass}>{errors.name}</p>}
                                </div>
                                <div>
                                    <input
                                        type="email"
                                        name="email"
                                        value={form.email}
                                        onChange={handleChange}
                                        placeholder="votre@email.com"
                                        className={inputClass}
                                    />
                                    {errors.email && <p className={errorClass}>{errors.email}</p>}
                                </div>
                            </div>

                            <div>
                                <input
                                    type="text"
                                    name="subject"
                                    value={form.subject}
                                    onChange={handleChange}
                                    placeholder="Sujet"
                                    className={inputClass}
                                />
                                {errors.subject && <p className={errorClass}>{errors.subject}</p>}
                            </div>

                            <div>
                                <textarea
                                    name="message"
                                    value={form.message}
                                    onChange={handleChange}
                                    placeholder="Votre message..."
                                    rows={6}
                                    className={inputClass + ' resize-none'}
                                />
                                {errors.message && <p className={errorClass}>{errors.message}</p>}
                            </div>

                            {status === 'error' && (
                                <p className="text-red-400 text-sm text-center">
                                    Une erreur est survenue. Veuillez réessayer.
                                </p>
                            )}

                            <button
                                type="submit"
                                disabled={status === 'sending'}
                                className="bg-[#FF701F] hover:bg-[#e5631a] disabled:opacity-50 text-white font-bold py-3 px-8 rounded-lg transition-all self-end"
                            >
                                {status === 'sending' ? 'Envoi...' : 'Envoyer →'}
                            </button>
                        </form>
                    )}
                </div>
            </div>

            <Footer />
        </div>
    );
}
