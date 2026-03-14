"use client";

import React from "react";

const CandidateGrid = ({
    candidates,
    scoresRef,
    onCandidateClick,
    submitted = false,
}) => {
    return (
        <div className="w-full p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 justify-center">
            {candidates.map((candidate, index) => {
                const defaultImage = "/candidates/linear.jfif";
                const genderFolder =
                    candidate.gender?.toLowerCase() || "unknown";
                const imageSrc = candidate.profile_img
                    ? candidate.profile_img
                    : `/candidates/${genderFolder}/${genderFolder}_${
                          index + 1
                      }.jpg`;

                const hasScore = scoresRef.current[candidate.id] !== undefined;
                const score = scoresRef.current[candidate.id] || candidate.existing_score;
                const isScored = hasScore || candidate.existing_score != null;

                return (
                    <div
                        key={candidate.id}
                        onClick={() => !submitted && !candidate.existing_score && onCandidateClick(candidate)}
                        className={`bg-neutral-900 border border-white/20 rounded-xl p-4 shadow-[0_4px_15px_rgba(255,255,255,0.3)] hover:shadow-[0_6px_25px_rgba(255,255,255,0.5)] transition-all duration-300 flex flex-col items-center gap-3 overflow-hidden ${
                            !submitted && !candidate.existing_score
                                ? "cursor-pointer hover:scale-105"
                                : "opacity-75"
                        } ${isScored ? "ring-2 ring-green-500" : ""}`}
                    >
                        <img
                            src={imageSrc || defaultImage}
                            alt={`${candidate.first_name} ${candidate.last_name}`}
                            className="w-full h-72 object-cover rounded-md"
                        />

                        <div className="text-center w-full overflow-hidden">
                            <p className="text-xs text-gray-400 mb-1">
                                # {candidate.candidate_number}
                            </p>
                            <h3 className="font-bold text-white truncate w-full px-2">
                                {candidate.first_name} {candidate.last_name}
                            </h3>
                        </div>

                        {isScored && (
                            <div className="w-full text-center">
                                <div className="bg-green-900/30 border border-green-700 rounded-lg py-2 px-3">
                                    <p className="text-xs text-gray-300 mb-1">Score</p>
                                    <p className="text-2xl font-bold text-green-400">
                                        {typeof score === 'number' ? score.toFixed(2) : score}
                                    </p>
                                </div>
                            </div>
                        )}

                        {!isScored && !submitted && (
                            <div className="w-full text-center">
                                <div className="bg-blue-900/30 border border-blue-700 rounded-lg py-2 px-3">
                                    <p className="text-sm text-blue-300">Click to Score</p>
                                </div>
                            </div>
                        )}
                    </div>
                );
            })}
        </div>
    );
};

export default CandidateGrid;
