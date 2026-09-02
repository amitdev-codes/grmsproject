import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Link } from '@inertiajs/react';
import { MapPinned, Menu, X, Sun, Moon } from 'lucide-react';
import React, { createContext, useContext, useState } from 'react';
import {route} from 'ziggy-js';

export const PRODUCT_NAME = 'GRMS';
export const SYSTEM_FULL_NAME_EN = 'Grievance Redress Management System';
export const SYSTEM_FULL_NAME_ST = 'Tsamaiso ea ho Rarolla Litletlebo';
export const AUTHORITY = 'Roads Directorate · Government of Lesotho';

// Routes — point these at your real Laravel routes.
export const LOGIN_URL = route('login');
export const FILE_GRIEVANCE_URL = route('file-grievance');
export const HOME_URL = route('home');
export const FAQ_URL = route('faq');
export const CONTACT_URL = route('contact');

export type IconType = React.ComponentType<{
    className?: string;
    style?: React.CSSProperties;
}>;

// ---------- Theme ----------

export type Theme = 'light' | 'dark';
export const ThemeContext = createContext<{ theme: Theme; toggle: () => void }>(
    {
        theme: 'light',
        toggle: () => {},
    },
);
export const useTheme = () => useContext(ThemeContext);

// ---------- Language ----------

export type Lang = 'en' | 'st';

export interface Translations {
    funding: string;
    nav: {
        product: string;
        howItWorks: string;
        whoItsFor: string;
        security: string;
        faqs: string;
        contact: string;
        signIn: string;
        fileGrievance: string;
    };
    hero: {
        eyebrow: string;
        headline: string[];
        sub: string;
        ctaPrimary: string;
        ctaSecondary: string;
        trust: string;
    };
    ticket: {
        stages: string[];
        sla: string;
        day: string;
        resolvedStamp: string;
        title: string;
        meta: string;
        dept: string;
    };
    chart: {
        heading: string;
        sub: string;
        note: string;
        legendReceived: string;
        legendResolved: string;
        trendHeading: string;
        statusHeading: string;
        ratioHeading: string;
        statusResolved: string;
        statusInProgress: string;
        statusPending: string;
        unresolved: string;
        resolvedLabel: string;
    };
    problem: {
        heading: string;
        withoutTitle: string;
        withTitle: string;
        rows: { old: string; grms: string }[];
    };
    features: {
        heading: string;
        sub: string;
        items: { title: string; desc: string }[];
    };
    how: { heading: string; steps: { title: string; desc: string }[] };
    audiences: { heading: string; tabs: { label: string; points: string[] }[] };
    security: { heading: string; items: { title: string; desc: string }[] };
    cta: { heading: string; sub: string; button: string };
    faqPage: {
        eyebrow: string;
        title: string;
        sub: string;
        items: { q: string; a: string }[];
    };
    contactPage: {
        eyebrow: string;
        title: string;
        sub: string;
        addressTitle: string;
        addressLines: string[];
        phoneLabel: string;
        phone: string;
        emailLabel: string;
        email: string;
        hoursLabel: string;
        hours: string;
        formHeading: string;
        fields: {
            name: string;
            email: string;
            mobile: string;
            subject: string;
            message: string;
        };
        submit: string;
        submitted: string;
    };
    footer: {
        tagline: string;
        colProduct: string;
        colProject: string;
        colContact: string;
        about: string;
        fundingLong: string;
    };
    auth: {
        login: {
            headTitle: string;
            title: string;
            subtitle: string;
            emailLabel: string;
            passwordLabel: string;
            forgotPassword: string;
            rememberMe: string;
            submit: string;
            noAccount: string;
            signUp: string;
        };
        register: {
            headTitle: string;
            title: string;
            subtitle: string;
            nameLabel: string;
            emailLabel: string;
            passwordLabel: string;
            confirmPasswordLabel: string;
            submit: string;
            haveAccount: string;
            signIn: string;
        };
    };
}

export const translations: Record<Lang, Translations> = {
    en: {
        funding:
            'Developed under the Lesotho Integrated Transport, Trade and Logistics (LITTL) Project — funded by the Government of Lesotho and the International Development Association (IDA), World Bank Group.',
        nav: {
            product: 'Product',
            howItWorks: 'How it works',
            whoItsFor: "Who it's for",
            security: 'Security',
            faqs: 'FAQs',
            contact: 'Contact us',
            signIn: 'Sign in',
            fileGrievance: 'File a grievance',
        },
        hero: {
            eyebrow: 'Roads Directorate · Grievance Redress Management System',
            headline: [
                'Every grievance travels a clear road',
                'to resolution.',
            ],
            sub: `${PRODUCT_NAME} gives the Roads Directorate one system to receive, route, track and close complaints from road users, communities and contractors along the LITTL corridor — with a record that stands up to review.`,
            ctaPrimary: 'File a grievance',
            ctaSecondary: 'See how it works',
            trust: 'Built for the LITTL Project — covering road works, land and resettlement queries, and contractor grievances.',
        },
        ticket: {
            stages: ['Filed', 'Acknowledged', 'Under review', 'Resolved'],
            sla: 'Target response: 5 working days',
            day: 'Day 3 of 5',
            resolvedStamp: 'Resolved',
            title: 'Pothole repair delayed on Mafeteng access road',
            meta: 'Filed by M. Ramabele · Category: Road maintenance',
            dept: 'Roads Directorate — Maseru district',
        },
        chart: {
            heading: 'Complaints and resolutions',
            sub: 'A running view of cases received against cases closed, month by month.',
            note: 'Sample data shown — connect these charts to your live case records.',
            legendReceived: 'Received',
            legendResolved: 'Resolved',
            trendHeading: 'Cumulative trend (year to date)',
            statusHeading: 'Case status breakdown',
            ratioHeading: 'Resolution rate',
            statusResolved: 'Resolved',
            statusInProgress: 'In progress',
            statusPending: 'Pending',
            unresolved: 'Unresolved',
            resolvedLabel: 'Resolved',
        },
        problem: {
            heading: 'The gap between a complaint and a resolution',
            withoutTitle: 'Without a system',
            withTitle: `With ${PRODUCT_NAME}`,
            rows: [
                {
                    old: 'Complaints arrive by phone, letter, site visit or word of mouth — with no shared record.',
                    grms: 'One intake, one case number, one accountable officer.',
                },
                {
                    old: 'Sensitive matters, like safety or conduct concerns, get handled the same way as routine queries.',
                    grms: 'Sensitive and confidential cases follow a restricted, separate channel.',
                },
                {
                    old: 'No consolidated record for project supervision or World Bank review.',
                    grms: 'Every status change is timestamped, exportable, and audit-ready.',
                },
            ],
        },
        features: {
            heading: 'What runs underneath the case number',
            sub: 'Six parts that turn a complaint into a closed, defensible case.',
            items: [
                {
                    title: 'Multi-channel intake',
                    desc: 'Web, mobile app, SMS, a toll-free hotline, and suggestion boxes at site offices and community liaison points along the corridor.',
                },
                {
                    title: 'Rule-based routing',
                    desc: 'Cases route automatically by district, road section, or category to the right Roads Directorate officer.',
                },
                {
                    title: 'SLA and escalation engine',
                    desc: 'Response and resolution timers per category; unresolved cases escalate on their own.',
                },
                {
                    title: 'Confidential case handling',
                    desc: 'A restricted channel for sensitive reports — including GBV/SEA concerns — visible only to designated caseworkers.',
                },
                {
                    title: 'Immutable audit trail',
                    desc: "Every status change, note and reassignment is timestamped and can't be edited away.",
                },
                {
                    title: 'Analytics and safeguards reporting',
                    desc: 'Dashboards and exportable logs by district, road corridor, category and case age — ready for supervision missions.',
                },
            ],
        },
        how: {
            heading: `How a grievance moves through ${PRODUCT_NAME}`,
            steps: [
                {
                    title: 'Submit',
                    desc: 'By web, app, SMS, hotline or suggestion box; the complainant gets a case number instantly.',
                },
                {
                    title: 'Acknowledge',
                    desc: 'Receipt is confirmed within days, not weeks; the response clock starts.',
                },
                {
                    title: 'Categorize and route',
                    desc: 'The case is sorted by type — works, land, labour, or sensitive — and sent to the right officer.',
                },
                {
                    title: 'Investigate and resolve',
                    desc: 'The officer records findings and the action taken, with evidence attached.',
                },
                {
                    title: 'Close, feedback and appeal',
                    desc: 'The complainant confirms resolution or requests a review if unsatisfied.',
                },
            ],
        },
        audiences: {
            heading: 'Built for everyone the LITTL corridor touches',
            tabs: [
                {
                    label: 'Communities & road users',
                    points: [
                        'Report road safety hazards, construction disruption, or access issues near works sites.',
                        'Track a case without a trip to the Roads Directorate office.',
                        'Use the system in Sesotho or English, by phone or in person.',
                    ],
                },
                {
                    label: 'Land & resettlement',
                    points: [
                        'Route compensation and resettlement queries to the designated safeguards officer.',
                        "Link a case to the project's land acquisition and resettlement register.",
                        'Sensitive claims are handled on a restricted, confidential channel.',
                    ],
                },
                {
                    label: 'Contractors & workers',
                    points: [
                        'Log labour, payment and workplace-safety grievances separately from public complaints.',
                        'Anonymous reporting for sensitive concerns, including GBV/SEA.',
                        'Contractors can respond to and close cases assigned to their site.',
                    ],
                },
                {
                    label: 'Roads Directorate & oversight',
                    points: [
                        'One dashboard for caseworkers across districts and road sections.',
                        'Consolidated reporting for World Bank implementation support missions.',
                        'Exportable GRM logs for safeguards audits and project reviews.',
                    ],
                },
            ],
        },
        security: {
            heading: 'Every case stands up to scrutiny',
            items: [
                {
                    title: 'Role-based access',
                    desc: 'Officers see only the cases assigned to their district or role.',
                },
                {
                    title: 'Encrypted storage',
                    desc: 'Data is encrypted at rest and in transit, with restricted access for sensitive cases.',
                },
                {
                    title: 'Audit-ready exports',
                    desc: 'Generate timestamped case reports for supervision missions or safeguards review.',
                },
            ],
        },
        cta: {
            heading: 'Ready to give every complaint a case number?',
            sub: "File a grievance in minutes, or sign in to track a case you've already submitted.",
            button: 'File a grievance',
        },
        faqPage: {
            eyebrow: 'Help centre',
            title: 'Frequently asked questions',
            sub: 'Answers about filing, tracking, and how the Roads Directorate handles your case.',
            items: [
                {
                    q: 'What counts as a grievance under the LITTL Project?',
                    a: 'Any complaint or concern about road works, land and resettlement, contractor conduct, or project impact — from a road user, community member, or worker.',
                },
                {
                    q: 'Can I report anonymously, especially for sensitive matters?',
                    a: 'Yes. Sensitive reports, including GBV/SEA concerns, can be filed anonymously through a restricted channel seen only by designated caseworkers.',
                },
                {
                    q: 'How are land and resettlement complaints handled?',
                    a: "They route directly to the safeguards officer and link to the project's land acquisition and resettlement register.",
                },
                {
                    q: 'Does the system work without a smartphone or internet access?',
                    a: 'Yes. Complaints can be filed by SMS, toll-free hotline, or in person at a site office or suggestion box.',
                },
                {
                    q: 'How does the World Bank review grievance data?',
                    a: 'Consolidated, exportable reports are available for implementation support missions and safeguards supervision.',
                },
                {
                    q: 'How long does it take to hear back after filing?',
                    a: "You'll receive an automatic acknowledgment within days, and a target response inside the SLA set for your complaint category.",
                },
            ],
        },
        contactPage: {
            eyebrow: 'Get in touch',
            title: 'Contact the Roads Directorate',
            sub: 'Reach us about a grievance, the LITTL Project, or a general enquiry.',
            addressTitle: 'Our office',
            addressLines: [
                'Roads Directorate',
                'Ministry of Public Works and Transport',
                'Maseru, Lesotho',
            ],
            phoneLabel: 'Phone',
            phone: '+266 XXXX XXXX',
            emailLabel: 'Email',
            email: 'grievances@roads.gov.ls',
            hoursLabel: 'Office hours',
            hours: 'Monday – Friday, 08:00 – 16:30',
            formHeading: 'Send us a message',
            fields: {
                name: 'Full name',
                email: 'Email address',
                mobile: 'Mobile number',
                subject: 'Subject',
                message: 'Message',
            },
            submit: 'Send message',
            submitted:
                "Thank you — your message has been sent. We'll respond within a few working days.",
        },
        footer: {
            tagline: SYSTEM_FULL_NAME_EN,
            colProduct: 'Product',
            colProject: 'Project',
            colContact: 'Contact',
            about: 'About the LITTL Project',
            fundingLong:
                'Developed under the Lesotho Integrated Transport, Trade and Logistics (LITTL) Project, funded by the Government of Lesotho and the International Development Association (IDA), World Bank Group.',
        },
        auth: {
            login: {
                headTitle: 'Log in',
                title: 'Log in to your account',
                subtitle: 'Enter your email and password below to log in',
                emailLabel: 'Email address',
                passwordLabel: 'Password',
                forgotPassword: 'Forgot your password?',
                rememberMe: 'Remember me',
                submit: 'Log in',
                noAccount: "Don't have an account?",
                signUp: 'Sign up',
            },
            register: {
                headTitle: 'Create an account',
                title: 'Create an account',
                subtitle: 'Enter your details below to create your account',
                nameLabel: 'Name',
                emailLabel: 'Email address',
                passwordLabel: 'Password',
                confirmPasswordLabel: 'Confirm password',
                submit: 'Create account',
                haveAccount: 'Already have an account?',
                signIn: 'Log in',
            },
        },
    },
    st: {
        funding:
            "E hlophisitsoe tlas'a Morero oa Lesotho Integrated Transport, Trade and Logistics (LITTL) — o tšehetsoang ke 'Muso oa Lesotho le International Development Association (IDA), sehlopha sa World Bank.",
        nav: {
            product: 'Sehlahiswa',
            howItWorks: 'Kamoo se sebetsang kateng',
            whoItsFor: 'Bao se etselitsoeng bona',
            security: "Ts'ireletso",
            faqs: 'Lipotso',
            contact: 'Ikopanye le rona',
            signIn: 'Kena',
            fileGrievance: 'Ngola tletlebo',
        },
        hero: {
            eyebrow: 'Roads Directorate · Tsamaiso ea ho Rarolla Litletlebo',
            headline: [
                "Tletlebo e 'ngoe le e 'ngoe e tsamaea",
                'tseleng e hlakileng ea tharollo.',
            ],
            sub: `${PRODUCT_NAME} e fa Roads Directorate tsamaiso e le 'ngoe ea ho amohela, ho romela, ho latela le ho koala litletlebo tsa basebelisi ba tsela, baahi le babeleki-boiketsong ba morero oa LITTL — e nang le tlaleho e emelang tlhahlobo.`,
            ctaPrimary: 'Ngola tletlebo',
            ctaSecondary: 'Bona kamoo se sebetsang kateng',
            trust: 'E entsoe bakeng sa Morero oa LITTL — e akaretsa mosebetsi oa litsela, lipotso tsa mobu le ho tlosoa, le litletlebo tsa babeleki-boiketsong.',
        },
        ticket: {
            stages: [
                'E kentsoe',
                'E amohetsoe',
                'E hlahlojoa',
                'E rarollotsoe',
            ],
            sla: 'Sepheo sa karabo: matsatsi a 5 a mosebetsi',
            day: 'Letsatsi la 3 ho tsoa ho 5',
            resolvedStamp: 'E rarollotsoe',
            title: 'Ho lokisoa ha likoti tseleng ea Mafeteng ho liehile',
            meta: 'E kentsoe ke M. Ramabele · Sehlopha: Tlhokomelo ea litsela',
            dept: 'Roads Directorate — Setereke sa Maseru',
        },
        chart: {
            heading: 'Litletlebo le Tharollo',
            sub: 'Pono ea linyeoe tse amohetsoeng khahlanong le tse koetsoeng, khoeli le khoeli.',
            note: 'Ke data ea mohlala — hokahanya lichate tsena le lirekoto tsa nyeoe tsa sebele.',
            legendReceived: 'Tse amohetsoeng',
            legendResolved: 'Tse rarolotsoeng',
            trendHeading: 'Tšusumetso e kopantsoeng (selemong sena)',
            statusHeading: 'Boemo ba linyeoe',
            ratioHeading: 'Sekhahla sa tharollo',
            statusResolved: 'Tse rarolotsoeng',
            statusInProgress: 'Tsa tsoelang pele',
            statusPending: 'Tse emetseng',
            unresolved: 'Tse sa rarolloang',
            resolvedLabel: 'Tse rarolotsoeng',
        },
        problem: {
            heading: 'Sekhala pakeng tsa tletlebo le tharollo',
            withoutTitle: 'Ntle le tsamaiso',
            withTitle: `Ka ${PRODUCT_NAME}`,
            rows: [
                {
                    old: 'Litletlebo li fihla ka mohala, lengolo, ketelo ea sebaka kapa ka lentsoe — ntle le tlaleho e arolelanoang.',
                    grms: "Kamohelo e le 'ngoe, nomoro e le 'ngoe ea nyeoe, moofisiri a ikarabellang.",
                },
                {
                    old: "Litaba tse bohlokoa, joalo ka ts'ireletso kapa boitšoaro, li tšoaroa ka tsela e ts'oanang le lipotso tse tloaelehileng.",
                    grms: 'Linyeoe tse bohlokoa li tsamaisoa ka mocha o arohaneng, o sirelelitsoeng.',
                },
                {
                    old: 'Ha ho na tlaleho e kopantsoeng bakeng sa tlhahlobo ea morero kapa ea World Bank.',
                    grms: "Phetoho e 'ngoe le e 'ngoe e na le nako, e ntšoa e le tlaleho, 'me e loketse tlhahlobo.",
                },
            ],
        },
        features: {
            heading: "Se sebetsang ka tlas'a nomoro ea nyeoe",
            sub: 'Likarolo tse tšeletseng tse fetolang tletlebo hore e be nyeoe e koetsoeng ka nepo.',
            items: [
                {
                    title: 'Kamohelo ka litsela tse ngata',
                    desc: 'Webosaete, app, SMS, mohala oa mahala, le mabokose a litletlebo liofising tsa sebaka le libakeng tsa boahi tsa morero.',
                },
                {
                    title: 'Tsamaiso ea melao ea ho romela',
                    desc: 'Linyeoe li ea liofising tse nepahetseng ho latela setereke, karolo ea tsela, kapa sehlopha ka boiketsetso.',
                },
                {
                    title: 'Nako e beiloeng le tsamaiso ea ho phahamisa',
                    desc: 'Nako ea karabo le tharollo e behiloe sehlopha se seng le se seng; linyeoe tse sa rarolloang li nyoloha ka boiketsetso.',
                },
                {
                    title: "Ts'ebetso ea linyeoe tse sirelelitsoeng",
                    desc: 'Mocha o arohaneng bakeng sa litlaleho tse bohlokoa — ho kenyeletsoa lipotso tsa GBV/SEA — o bonoang feela ke basebetsi ba khethiloeng.',
                },
                {
                    title: 'Tlaleho e sa fetoheng',
                    desc: "Phetoho e 'ngoe le e 'ngoe, tlhaloso le phetiso li ngoliloe 'me li ke ke tsa fetoloa.",
                },
                {
                    title: "Tlhahlobo le litlaleho tsa ts'ireletso",
                    desc: 'Dashboard le litlaleho tse ntšoang ka setereke, karolo ea tsela, sehlopha le nako — tse loketseng maeto a tlhahlobo.',
                },
            ],
        },
        how: {
            heading: `Kamoo tletlebo e tsamaeang ka ${PRODUCT_NAME}`,
            steps: [
                {
                    title: 'Romela',
                    desc: 'Ka webosaete, app, SMS, mohala kapa lebokose la litletlebo; moetseletsi o fumana nomoro ea nyeoe hang-hang.',
                },
                {
                    title: 'Amohela',
                    desc: 'Kamohelo e netefatsoa ka matsatsi, eseng libeke; nako ea karabo e qala.',
                },
                {
                    title: 'Arola le ho romela',
                    desc: 'Nyeoe e aroloa ka mofuta — mosebetsi, mobu, basebetsi, kapa e sa tšoaneleng — mme e romelloa moofisiri ea nepahetseng.',
                },
                {
                    title: 'Hlahloba le ho rarolla',
                    desc: 'Moofisiri o ngola liphuputso le liketso tse entsoeng, hammoho le bopaki.',
                },
                {
                    title: 'Koala, tlaleho le boipiletso',
                    desc: 'Moetseletsi o netefatsa tharollo kapa o kopa tlhahlobo ha a sa khotsofala.',
                },
            ],
        },
        audiences: {
            heading: 'E entsoe bakeng sa bohle ba amanang le morero oa LITTL',
            tabs: [
                {
                    label: 'Baahi le basebelisi ba tsela',
                    points: [
                        "Tlaleha likotsi tsa polokeho tseleng, ts'itiso ea kaho, kapa mathata a phihlello haufi le libaka tsa mosebetsi.",
                        'Latela nyeoe ntle le ho ea ofising ea Roads Directorate.',
                        'Sebelisa tsamaiso ka Sesotho kapa Senyesemane, ka mohala kapa ka sebele.',
                    ],
                },
                {
                    label: 'Mobu le ho tlosoa',
                    points: [
                        "Romela lipotso tsa tefo le ho tlosoa moofisiring ea ts'ireletso ea khethiloeng.",
                        'Hokahanya nyeoe le lengolo la morero la ho fumana mobu le ho tlosoa.',
                        'Litletlebo tse bohlokoa li tšoaroa ka mocha o arohaneng, o sirelelitsoeng.',
                    ],
                },
                {
                    label: 'Babeleki-boiketsong le basebetsi',
                    points: [
                        "Ngola litletlebo tsa basebetsi, tefo le ts'ireletso mosebetsing ka thoko ho tse tsoang ho sechaba.",
                        'Tlaleho e sa tsejoeng bakeng sa litaba tse bohlokoa, ho kenyeletsoa GBV/SEA.',
                        'Babeleki-boiketsong ba ka araba le ho koala linyeoe tse abetsoeng sebaka sa bona.',
                    ],
                },
                {
                    label: 'Roads Directorate le tlhahlobo',
                    points: [
                        "Dashboard e le 'ngoe bakeng sa basebetsi mererong yohle le litereke.",
                        "Tlaleho e kopantsoeng bakeng sa maeto a tšehetso ea ts'ebetsong ea World Bank.",
                        "Litlaleho tse ntšoang bakeng sa tlhahlobo ea ts'ireletso le ea morero.",
                    ],
                },
            ],
        },
        security: {
            heading: "Nyeoe e 'ngoe le e 'ngoe e emela tlhahlobo",
            items: [
                {
                    title: 'Phihlello e ipapisitseng le boemo',
                    desc: 'Liofisiri li bona feela linyeoe tse abetsoeng setereke kapa mosebetsi oa bona.',
                },
                {
                    title: 'Polokelo e sirelelitsoeng',
                    desc: "Data e sirelelitsoe ha e le sebakeng le ha e tsamaea, 'me linyeoe tse bohlokoa li na le phihlello e fokolang.",
                },
                {
                    title: 'Litlaleho tse loketseng tlhahlobo',
                    desc: "Hlahisa litlaleho tsa linyeoe tse nang le nako bakeng sa maeto a tšehetso kapa tlhahlobo ea ts'ireletso.",
                },
            ],
        },
        cta: {
            heading:
                "Na u loketse ho fa tletlebo e 'ngoe le e 'ngoe nomoro ea nyeoe?",
            sub: 'Ngola tletlebo ka metsotso, kapa u kene ho latela nyeoe eo u seng u e ile ua e romela.',
            button: 'Ngola tletlebo',
        },
        faqPage: {
            eyebrow: 'Setsi sa thuso',
            title: 'Lipotso tse botsoang khafetsa',
            sub: 'Likarabo ka ho ngola, ho latela, le kamoo Roads Directorate e tsamaisang nyeoe ea hau kateng.',
            items: [
                {
                    q: "Ke eng se balloang tletlebo tlas'a Morero oa LITTL?",
                    a: 'Tletlebo leha e le efe mabapi le mosebetsi oa tsela, mobu le ho tlosoa, boitšoaro ba babeleki-boiketsong, kapa liphello tsa morero — ho tsoa ho mosebelisi oa tsela, moahi, kapa mosebetsi.',
                },
                {
                    q: 'Na nka tlaleha ntle le ho tsejoa, haholo bakeng sa litaba tse bohlokoa?',
                    a: 'E, ho joalo. Litlaleho tse bohlokoa, ho kenyeletsoa GBV/SEA, li ka romelloa ka mocha o sirelelitsoeng, o bonoang feela ke basebetsi ba khethiloeng.',
                },
                {
                    q: 'Litletlebo tsa mobu le ho tlosoa li tšoaroa joang?',
                    a: "Li romelloa ka ho toba ho moofisiri oa ts'ireletso 'me li hokahantsoe le lengolo la morero la ho fumana mobu le ho tlosoa.",
                },
                {
                    q: 'Na tsamaiso e sebetsa ntle le founo e bohlale kapa inthanete?',
                    a: 'E, ho joalo. Litletlebo li ka romelloa ka SMS, mohala oa mahala, kapa ka sebele ofising ea sebaka kapa lebokoseng la litletlebo.',
                },
                {
                    q: 'World Bank e hlahloba data ea litletlebo joang?',
                    a: "Litlaleho tse kopantsoeng, tse ntšoang, li fumaneha bakeng sa maeto a tšehetso ea ts'ebetso le tlhahlobo ea ts'ireletso.",
                },
                {
                    q: 'Ho nka nako e kae ho fumana karabo kamora ho ngola?',
                    a: "U tla fumana kamohelo ea boiketsetso ka matsatsi, le sepheo sa karabo ka tlas'a nako e beiloeng bakeng sa sehlopha sa tletlebo ea hau.",
                },
            ],
        },
        contactPage: {
            eyebrow: 'Ikopanye le rona',
            title: 'Ikopanye le Roads Directorate',
            sub: 'Re fihlele ka tletlebo, ka Morero oa LITTL, kapa ka potso e akaretsang.',
            addressTitle: 'Ofisi ea rona',
            addressLines: [
                'Roads Directorate',
                'Ministry of Public Works and Transport',
                'Maseru, Lesotho',
            ],
            phoneLabel: 'Mohala',
            phone: '+266 XXXX XXXX',
            emailLabel: 'Imeile',
            email: 'grievances@roads.gov.ls',
            hoursLabel: 'Nako tsa ofisi',
            hours: 'Mantaha – Labohlano, 08:00 – 16:30',
            formHeading: 'Re romelle molaetsa',
            fields: {
                name: 'Lebitso le felletseng',
                email: 'Aterese ea imeile',
                mobile: 'Nomoro ea mohala',
                subject: 'Sehlooho',
                message: 'Molaetsa',
            },
            submit: 'Romela molaetsa',
            submitted:
                "Kea leboha — molaetsa oa hau o romelitsoe. Re tla araba ka har'a matsatsi a seng makae a mosebetsi.",
        },
        footer: {
            tagline: SYSTEM_FULL_NAME_ST,
            colProduct: 'Sehlahiswa',
            colProject: 'Morero',
            colContact: 'Ikopanye',
            about: 'Ka Morero oa LITTL',
            fundingLong:
                "E hlophisitsoe tlas'a Morero oa Lesotho Integrated Transport, Trade and Logistics (LITTL), o tšehetsoang ke 'Muso oa Lesotho le International Development Association (IDA), sehlopha sa World Bank.",
        },
        auth: {
            login: {
                headTitle: 'Kena',
                title: "Kena ho ak'aonto ea hau",
                subtitle: 'Kenya imeile le password ea hau ho kena',
                emailLabel: 'Aterese ea imeile',
                passwordLabel: 'Password',
                forgotPassword: 'U lebetse password ea hau?',
                rememberMe: 'Nkgopotse',
                submit: 'Kena',
                noAccount: "Ha u na ak'aonto?",
                signUp: 'Ngodisa',
            },
            register: {
                headTitle: "Etsa ak'aonto",
                title: "Etsa ak'aonto",
                subtitle:
                    "Kenya lintlha tsa hau ka tlase ho theha ak'aonto ea hau",
                nameLabel: 'Lebitso',
                emailLabel: 'Aterese ea imeile',
                passwordLabel: 'Password',
                confirmPasswordLabel: 'Netefatsa password',
                submit: "Theha ak'aonto",
                haveAccount: "Na u se u na le ak'aonto?",
                signIn: 'Kena',
            },
        },
    },
};

export const LanguageContext = createContext<{
    lang: Lang;
    t: Translations;
    toggle: () => void;
}>({
    lang: 'en',
    t: translations.en,
    toggle: () => {},
});
export const useI18n = () => useContext(LanguageContext);

// ---------- Global styles ----------

export function FontStyles() {
    return (
        <style>{`
      @import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap');
      .grms-root {
        --bg-page: #EFEFE6;
        --bg-raised: #F8F8F2;
        --bg-inverse: #14213D;
        --bg-page-translucent: rgba(239,239,230,0.92);
        --text-primary: #14213D;
        --text-secondary: #4E5A72;
        --text-on-inverse: #ECEAE0;
        --text-on-inverse-secondary: #A9B4C9;
        --border: #D6D5C7;
        --accent: #B8763A;
        --accent-dark: #8F5A28;
        --resolved: #1E7145;
        --resolved-bg: #E1EFE6;
        --ridge-1: #C7CBBE;
        --ridge-2: #B3B8A6;
        --ridge-3: #9CA290;
        font-family: 'IBM Plex Sans', sans-serif;
        color: var(--text-primary);
        background: var(--bg-page);
        transition: background 0.2s ease, color 0.2s ease;
      }
      .grms-root.dark {
        --bg-page: #10182B;
        --bg-raised: #182238;
        --bg-inverse: #080C16;
        --bg-page-translucent: rgba(16,24,43,0.92);
        --text-primary: #ECEAE0;
        --text-secondary: #A7B0C4;
        --text-on-inverse: #ECEAE0;
        --text-on-inverse-secondary: #8790A6;
        --border: #2A3350;
        --accent: #D89552;
        --accent-dark: #EAB273;
        --resolved: #4CA37A;
        --resolved-bg: #16281F;
        --ridge-1: #1C2540;
        --ridge-2: #212C4A;
        --ridge-3: #263254;
      }
      .grms-root .font-display { font-family: 'Source Serif 4', serif; }
      .grms-root .font-mono { font-family: 'IBM Plex Mono', monospace; }
      @media (prefers-reduced-motion: reduce) {
        .grms-road-line { animation: none !important; }
      }
      @keyframes grms-road-draw {
        from { stroke-dashoffset: 240; }
        to { stroke-dashoffset: 0; }
      }
      .grms-road-line {
        stroke-dasharray: 6 6;
        animation: grms-road-draw 6s linear infinite;
      }
      .grms-root input, .grms-root textarea {
        background: var(--bg-raised);
        border: 1px solid var(--border);
        color: var(--text-primary);
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 14px;
        width: 100%;
        outline: none;
      }
      .grms-root input:focus, .grms-root textarea:focus {
        border-color: var(--accent);
      }
    `}</style>
    );
}

// ---------- Decorative backgrounds (grievance / road / resolution motif) ----------

/** Full hero backdrop: mountain ridges, a winding road, and small "resolved" waypoint markers. */
export function MountainRoadBackdrop() {
    return (
        <svg
            viewBox="0 0 600 420"
            className="absolute inset-0 h-full w-full"
            preserveAspectRatio="xMidYMax slice"
            aria-hidden="true"
            style={{ opacity: 0.55 }}
        >
            <path
                d="M0 300 L80 210 L150 270 L230 160 L300 260 L370 190 L460 280 L540 220 L600 290 L600 420 L0 420 Z"
                fill="var(--ridge-1)"
            />
            <path
                d="M0 340 L100 260 L190 320 L260 240 L340 320 L420 250 L500 330 L600 260 L600 420 L0 420 Z"
                fill="var(--ridge-2)"
            />
            <path
                d="M0 380 L120 320 L210 370 L300 310 L390 375 L480 320 L600 380 L600 420 L0 420 Z"
                fill="var(--ridge-3)"
            />
            <path
                d="M0 405 C 90 380, 140 420, 230 385 S 380 350, 460 390 S 560 400, 600 380"
                fill="none"
                stroke="var(--accent)"
                strokeWidth="3"
                className="grms-road-line"
            />
            {/* resolution waypoints along the road */}
            {[
                { x: 140, y: 407 },
                { x: 340, y: 358 },
                { x: 520, y: 397 },
            ].map((p, i) => (
                <g key={i} transform={`translate(${p.x} ${p.y})`}>
                    <circle
                        r="9"
                        fill="var(--resolved-bg)"
                        stroke="var(--resolved)"
                        strokeWidth="1.5"
                    />
                    <path
                        d="M-3.5 0 L-1 2.5 L4 -3"
                        stroke="var(--resolved)"
                        strokeWidth="1.6"
                        fill="none"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </g>
            ))}
            {/* a small "grievance" document marker */}
            <g transform="translate(70 60)" opacity="0.8">
                <rect
                    x="0"
                    y="0"
                    width="26"
                    height="32"
                    rx="2"
                    fill="var(--bg-raised)"
                    stroke="var(--border)"
                />
                <line
                    x1="5"
                    y1="8"
                    x2="21"
                    y2="8"
                    stroke="var(--text-secondary)"
                    strokeWidth="1.5"
                />
                <line
                    x1="5"
                    y1="14"
                    x2="21"
                    y2="14"
                    stroke="var(--text-secondary)"
                    strokeWidth="1.5"
                />
                <line
                    x1="5"
                    y1="20"
                    x2="15"
                    y2="20"
                    stroke="var(--text-secondary)"
                    strokeWidth="1.5"
                />
            </g>
        </svg>
    );
}

/** Restrained low-opacity road accent, reused as a secondary-page header background. */
export function RoadWatermark() {
    return (
        <svg
            viewBox="0 0 1000 200"
            className="absolute inset-0 h-full w-full"
            preserveAspectRatio="xMidYMax slice"
            aria-hidden="true"
            style={{ opacity: 0.3 }}
        >
            <path
                d="M0 190 L150 130 L260 175 L400 100 L520 170 L650 110 L780 180 L1000 120 L1000 200 L0 200 Z"
                fill="var(--ridge-1)"
            />
            <path
                d="M0 160 C 150 190, 300 130, 460 170 S 720 190, 1000 150"
                fill="none"
                stroke="var(--accent)"
                strokeWidth="3"
                className="grms-road-line"
            />
        </svg>
    );
}

/** Divider used to connect sequential steps — the "road" the grievance travels. */
export function RoadDivider() {
    return (
        <svg
            viewBox="0 0 1000 40"
            className="hidden w-full md:block"
            style={{ height: 24 }}
            aria-hidden="true"
        >
            <line
                x1="20"
                y1="20"
                x2="980"
                y2="20"
                stroke="var(--border)"
                strokeWidth="2"
                strokeDasharray="1 10"
                strokeLinecap="round"
            />
            {[20, 265, 510, 755].map((x) => (
                <circle key={x} cx={x} cy="20" r="4" fill="var(--accent)" />
            ))}
        </svg>
    );
}

// ---------- Page shell (theme + language providers) ----------

export function PageShell({ children }: { children: React.ReactNode }) {
    const [theme, setTheme] = useState<Theme>('light');
    const [lang, setLang] = useState<Lang>('en');
    const themeValue = {
        theme,
        toggle: () => setTheme((v) => (v === 'light' ? 'dark' : 'light')),
    };
    const langValue = {
        lang,
        t: translations[lang],
        toggle: () => setLang((v) => (v === 'en' ? 'st' : 'en')),
    };

    return (
        <ThemeContext.Provider value={themeValue}>
            <LanguageContext.Provider value={langValue}>
                <Shell>{children}</Shell>
            </LanguageContext.Provider>
        </ThemeContext.Provider>
    );
}

function Shell({ children }: { children: React.ReactNode }) {
    const { theme } = useTheme();

    return (
        <div
            className={`grms-root min-h-screen ${theme === 'dark' ? 'dark' : ''}`}
        >
            <FontStyles />
            {children}
        </div>
    );
}

// ---------- Nav ----------

// change from a private function to an exported one — everything else in the file stays identical
export function LanguageToggle() {
    const { lang, toggle } = useI18n();

    return (
        <button
            onClick={toggle}
            className="flex items-center gap-1.5 rounded-full border px-2 py-1 text-xs font-medium"
            style={{
                borderColor: 'var(--border)',
                color: 'var(--text-secondary)',
            }}
            aria-label="Switch language"
        >
            <span style={{ opacity: lang === 'en' ? 1 : 0.4 }}>🇬🇧 EN</span>
            <span style={{ color: 'var(--border)' }}>/</span>
            <span style={{ opacity: lang === 'st' ? 1 : 0.4 }}>🇱🇸 ST</span>
        </button>
    );
}

function ThemeToggle() {
    const { theme, toggle } = useTheme();

    return (
        <button
            onClick={toggle}
            className="flex h-8 w-8 items-center justify-center rounded-full border"
            style={{
                borderColor: 'var(--border)',
                color: 'var(--text-secondary)',
            }}
            aria-label="Toggle dark mode"
        >
            {theme === 'light' ? (
                <Moon className="h-4 w-4" />
            ) : (
                <Sun className="h-4 w-4" />
            )}
        </button>
    );
}

export function NavBar() {
    const [open, setOpen] = useState(false);
    const { t } = useI18n();
    const links = [
        { label: t.nav.product, href: `${HOME_URL}#features` },
        { label: t.nav.howItWorks, href: `${HOME_URL}#how-it-works` },
        { label: t.nav.whoItsFor, href: `${HOME_URL}#audiences` },
        { label: t.nav.security, href: `${HOME_URL}#security` },
        { label: t.nav.faqs, href: FAQ_URL },
        { label: t.nav.contact, href: CONTACT_URL },
    ];

    return (
        <header
            className="sticky top-0 z-30 border-b"
            style={{
                borderColor: 'var(--border)',
                background: 'var(--bg-page-translucent)',
                backdropFilter: 'blur(6px)',
            }}
        >
            <div
                className="px-4 py-2 text-center text-xs"
                style={{
                    background: 'var(--bg-inverse)',
                    color: 'var(--text-on-inverse-secondary)',
                }}
            >
                {t.funding}
            </div>
            <div className="mx-auto grid max-w-7xl grid-cols-[auto_1fr_auto] items-center gap-4 px-6 py-3.5">
                {/* Left: logo + project name */}
                <Link
                    href={HOME_URL}
                    className="flex shrink-0 items-center gap-2.5"
                >
                    <div
                        className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm"
                        style={{ background: '#14213D' }}
                    >
                        <MapPinned
                            className="h-4.5 w-4.5"
                            style={{ color: '#EFEFE6' }}
                        />
                    </div>
                    <div className="hidden leading-tight sm:block">
                        <div className="font-display text-lg font-semibold tracking-tight whitespace-nowrap">
                            {PRODUCT_NAME}
                        </div>
                        <div
                            className="text-[11px] whitespace-nowrap"
                            style={{ color: 'var(--text-secondary)' }}
                        >
                            {AUTHORITY}
                        </div>
                    </div>
                </Link>

                {/* Center: menu */}
                <nav className="hidden min-w-0 items-center justify-center gap-5 lg:flex">
                    {links.map((l) => (
                        <Link
                            key={l.label}
                            href={l.href}
                            className="text-sm font-medium whitespace-nowrap transition-opacity hover:opacity-70"
                            style={{ color: 'var(--text-secondary)' }}
                        >
                            {l.label}
                        </Link>
                    ))}
                </nav>

                {/* Right: sign in, theme, language */}
                <div className="hidden shrink-0 items-center gap-3 justify-self-end lg:flex">
                    <Button
                        variant="ghost"
                        className="text-sm whitespace-nowrap"
                        style={{ color: 'var(--text-primary)' }}
                    >
                        <Link href={LOGIN_URL}>
                        {t.nav.signIn}
                        </Link>
                    </Button>

                    <Separator
                        orientation="vertical"
                        className="h-5"
                        style={{ background: 'var(--border)' }}
                    />
                    <ThemeToggle />
                    <LanguageToggle />
                </div>

                <div className="flex items-center gap-2 justify-self-end lg:hidden">
                    <ThemeToggle />
                    <LanguageToggle />
                    <button
                        onClick={() => setOpen(!open)}
                        aria-label="Toggle menu"
                    >
                        {open ? (
                            <X className="h-5 w-5" />
                        ) : (
                            <Menu className="h-5 w-5" />
                        )}
                    </button>
                </div>
            </div>

            {open && (
                <div
                    className="flex flex-col gap-4 border-t px-6 py-4 lg:hidden"
                    style={{
                        borderColor: 'var(--border)',
                        background: 'var(--bg-page)',
                    }}
                >
                    {links.map((l) => (
                        <Link
                            key={l.label}
                            href={l.href}
                            className="text-sm font-medium"
                            onClick={() => setOpen(false)}
                        >
                            {l.label}
                        </Link>
                    ))}
                        <Button
                            variant="outline"
                            className="w-full"
                            style={{ borderColor: 'var(--border)' }}
                        >      <Link href={LOGIN_URL}>
                            {t.nav.signIn}
                        </Link>
                        </Button>
                </div>
            )}
        </header>
    );
}

// ---------- Footer (column layout) ----------

export function Footer() {
    const { t } = useI18n();

    return (
        <footer className="border-t" style={{ borderColor: 'var(--border)' }}>
            <div className="mx-auto grid max-w-6xl grid-cols-2 gap-8 px-6 py-12 md:grid-cols-4">
                <div className="col-span-2 md:col-span-1">
                    <div className="mb-3 flex items-center gap-2">
                        <MapPinned
                            className="h-4 w-4"
                            style={{ color: 'var(--accent)' }}
                        />
                        <span className="font-display font-semibold">
                            {PRODUCT_NAME}
                        </span>
                    </div>
                    <p
                        className="mb-3 text-xs"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.footer.tagline}
                    </p>
                    <p
                        className="text-xs leading-relaxed"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.footer.fundingLong}
                    </p>
                </div>

                <div className="space-y-2">
                    <p
                        className="mb-3 text-xs font-semibold tracking-wide uppercase"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.footer.colProduct}
                    </p>
                    <Link
                        href={`${HOME_URL}#features`}
                        className="block text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.nav.product}
                    </Link>
                    <Link
                        href={`${HOME_URL}#how-it-works`}
                        className="block text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.nav.howItWorks}
                    </Link>
                    <Link
                        href={`${HOME_URL}#audiences`}
                        className="block text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.nav.whoItsFor}
                    </Link>
                    <Link
                        href={`${HOME_URL}#security`}
                        className="block text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.nav.security}
                    </Link>
                </div>

                <div className="space-y-2">
                    <p
                        className="mb-3 text-xs font-semibold tracking-wide uppercase"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.footer.colProject}
                    </p>
                    <Link
                        href={HOME_URL}
                        className="block text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.footer.about}
                    </Link>
                    <Link
                        href={FAQ_URL}
                        className="block text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.nav.faqs}
                    </Link>
                    <Link
                        href={CONTACT_URL}
                        className="block text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.nav.contact}
                    </Link>
                </div>

                <div className="space-y-2">
                    <p
                        className="mb-3 text-xs font-semibold tracking-wide uppercase"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.footer.colContact}
                    </p>
                    {t.contactPage.addressLines.map((line) => (
                        <p
                            key={line}
                            className="text-sm"
                            style={{ color: 'var(--text-secondary)' }}
                        >
                            {line}
                        </p>
                    ))}
                    <p
                        className="text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.contactPage.email}
                    </p>
                    <p
                        className="text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.contactPage.phone}
                    </p>
                </div>
            </div>
            <div
                className="border-t py-4 text-center text-xs"
                style={{
                    borderColor: 'var(--border)',
                    color: 'var(--text-secondary)',
                }}
            >
                © {new Date().getFullYear()} {AUTHORITY}
            </div>
        </footer>
    );
}
