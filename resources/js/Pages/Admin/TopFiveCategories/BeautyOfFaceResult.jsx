"use client";

import React from "react";
import PageLayout from "@/Layouts/PageLayout";
import ResultTable from "@/Pages/Admin/Partials/ResultTable";

const BeautyOfFaceResult = ({
    categoryName = "Beauty of Face",
    candidates = [],
    judgeOrder = [],
}) => {
    return (
        <PageLayout>
            <h2 className="text-white text-xl font-bold mb-6 flex justify-center mt-6">
                {categoryName} Results
            </h2>

            <ResultTable
                candidates={candidates}
                judgeOrder={judgeOrder}
                category="beauty_of_face"
                pdfRoute={route('admin.beauty_of_face.pdf')}
                maxPoints={15}
                isAverageScore={true}
            />
        </PageLayout>
    );
};

export default BeautyOfFaceResult;
