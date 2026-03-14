"use client";

import React, { useState, useEffect } from "react";

const CriteriaScoreModal = ({
    isOpen,
    onClose,
    candidate,
    criteria,
    onSubmit,
    existingScores = {},
}) => {
    const [scores, setScores] = useState({});
    const [totalScore, setTotalScore] = useState(0);

    useEffect(() => {
        if (isOpen && candidate) {
            const initialScores = {};
            criteria.forEach((criterion, index) => {
                initialScores[index] = existingScores[index] || "";
            });
            setScores(initialScores);
        }
    }, [isOpen, candidate?.id]); // Only depend on isOpen and candidate.id

    useEffect(() => {
        const total = Object.values(scores).reduce(
            (sum, score) => sum + (parseFloat(score) || 0),
            0
        );
        setTotalScore(total);
    }, [scores]);

    const handleScoreChange = (index, value) => {
        console.log('Score change:', index, value); // Debug log
        const maxPoints = criteria[index].maxPoints;
        const numValue = parseFloat(value);
        
        if (value !== "" && !isNaN(numValue) && numValue > maxPoints) {
            value = maxPoints.toString();
        }
        
        setScores(prev => {
            const newScores = { ...prev, [index]: value };
            console.log('New scores:', newScores); // Debug log
            return newScores;
        });
    };

    const handleSubmit = () => {
        const inputElements = document.querySelectorAll('input[type="number"]');
        const newScores = {};
        let calculatedTotal = 0;
        
        for (let i = 0; i < inputElements.length; i++) {
            const value = inputElements[i].value;
            newScores[i] = value;
            calculatedTotal += parseFloat(value) || 0;
        }

        const allFilled = criteria.every(
            (_, index) => newScores[index] !== "" && newScores[index] !== undefined
        );

        if (!allFilled) {
            alert("Please fill in all criteria scores!");
            return;
        }

        for (let i = 0; i < criteria.length; i++) {
            const score = parseFloat(newScores[i]);
            const maxPoints = criteria[i].maxPoints;
            
            if (isNaN(score) || score < 0 || score > maxPoints) {
                alert(`Invalid score for criterion ${i + 1}. Must be between 0 and ${maxPoints}.`);
                return;
            }
        }

        onSubmit(candidate.id, newScores, calculatedTotal);
        onClose();
    };

    if (!isOpen || !candidate) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div 
                className="absolute inset-0 bg-black/80"
                onClick={onClose}
            />
            
            <div 
                className="relative z-50 w-full max-w-5xl max-h-[90vh] bg-neutral-900 text-white rounded-xl shadow-2xl flex flex-col overflow-hidden"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Header */}
                <div className="bg-gradient-to-r from-blue-900 to-purple-900 px-6 py-4 border-b border-white/10">
                    <h2 className="text-2xl font-bold">
                        Score Candidate #{candidate.candidate_number}
                    </h2>
                    <p className="text-gray-200 text-sm mt-1">
                        {candidate.first_name} {candidate.last_name}
                    </p>
                </div>

                {/* Main Content - Two Column Layout */}
                <div className="flex flex-1 overflow-hidden">
                    {/* Left Side - Candidate Photo */}
                    <div className="w-80 bg-neutral-800 p-6 flex flex-col items-center justify-start border-r border-white/10">
                        <div className="w-full aspect-[3/4] rounded-lg overflow-hidden shadow-xl mb-4 border-2 border-white/20">
                            <img
                                src={candidate.profile_img || "/default-avatar.png"}
                                alt={`${candidate.first_name} ${candidate.last_name}`}
                                className="w-full h-full object-cover"
                            />
                        </div>
                        <div className="text-center">
                            <p className="text-3xl font-bold text-blue-400 mb-2">
                                #{candidate.candidate_number}
                            </p>
                            <p className="text-lg font-semibold text-white">
                                {candidate.first_name}
                            </p>
                            <p className="text-lg font-semibold text-white">
                                {candidate.last_name}
                            </p>
                        </div>
                    </div>

                    {/* Right Side - Scoring Form */}
                    <div className="flex-1 flex flex-col">
                        <div className="flex-1 p-6">
                            <h3 className="text-lg font-semibold text-gray-200 mb-4 flex items-center">
                                <span className="w-1 h-6 bg-blue-500 mr-3 rounded"></span>
                                Scoring Criteria
                            </h3>
                            <div className="space-y-3">
                                {criteria.map((criterion, index) => (
                                    <div
                                        key={index}
                                        className="bg-neutral-800/50 p-3 rounded-lg border border-gray-700 hover:border-blue-500/50 transition-colors"
                                    >
                                        <div className="flex justify-between items-start mb-2">
                                            <label className="text-sm font-medium text-gray-100 flex-1 leading-relaxed">
                                                <span className="inline-block w-6 h-6 bg-blue-600 text-white rounded-full text-center text-xs leading-6 mr-2">
                                                    {index + 1}
                                                </span>
                                                {criterion.name}
                                            </label>
                                            <span className="text-xs font-semibold text-blue-400 ml-3 bg-blue-900/30 px-2 py-1 rounded">
                                                Max: {criterion.maxPoints}
                                            </span>
                                        </div>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max={criterion.maxPoints}
                                            value={scores[index] || ""}
                                            onChange={(e) => {
                                                e.stopPropagation();
                                                handleScoreChange(index, e.target.value);
                                            }}
                                            onFocus={(e) => e.stopPropagation()}
                                            onClick={(e) => e.stopPropagation()}
                                            className="w-full px-4 py-2 bg-neutral-700 border-2 border-gray-600 rounded-lg text-white text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder={`0 - ${criterion.maxPoints}`}
                                            autoComplete="off"
                                        />
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Footer with Buttons */}
                        <div className="border-t border-white/10 px-6 py-4 bg-neutral-800/50">
                            <div className="flex justify-end gap-3">
                                <button
                                    type="button"
                                    className="px-6 py-3 bg-neutral-700 hover:bg-neutral-600 text-white font-semibold rounded-lg transition-colors"
                                    onClick={onClose}
                                >
                                    Cancel
                                </button>

                                <button
                                    type="button"
                                    className="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                                    onClick={handleSubmit}
                                >
                                    Submit Score
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CriteriaScoreModal;
