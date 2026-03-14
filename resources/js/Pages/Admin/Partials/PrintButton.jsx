"use client";

import React from "react";
import { HoverBorderGradient } from "@/Components/ui/hover-border-gradient";

const PrintButton = ({ pdfRoute }) => {
    const handleDownloadPDF = () => {
        window.location.href = pdfRoute;
    };

    return (
        <HoverBorderGradient
            containerClassName="rounded-full"
            as="button"
            className="dark:bg-neutral-800 bg-white text-black dark:text-neutral-100 flex items-center space-x-2 px-12 py-1 text-lg font-semibold"
            onClick={handleDownloadPDF}
        >
            <span>Download PDF</span>
        </HoverBorderGradient>
    );
};

export default PrintButton;
