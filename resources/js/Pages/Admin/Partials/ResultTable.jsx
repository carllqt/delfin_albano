"use client";

import React from "react";
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableCell,
    TableHead,
    TableCaption,
} from "@/components/ui/table";
import PrintButton from "./PrintButton";

const ResultTable = ({ title, candidates, judgeOrder, category, pdfRoute, maxPoints = 100, isAverageScore = false }) => {
    const tableRef = React.useRef();

    return (
        <div className="p-4 mb-8">
            <div className="flex justify-between items-center mb-4">
                <h2 className="text-white text-xl font-bold mb-4">{title}</h2>
                <PrintButton pdfRoute={pdfRoute} />
            </div>

            {/* Include the heading inside the printable area */}
            <div ref={tableRef}>
                <h2 className="text-center text-2xl font-bold text-black bg-gray-100 py-4 print:block hidden">
                    {title} Results
                </h2>

                <Table className="bg-black text-white border border-gray-600">
                    <TableCaption className="text-white">{title}</TableCaption>
                    <TableHeader>
                        <TableRow className="bg-black">
                            <TableHead className="text-white">#</TableHead>
                            <TableHead className="text-white">Candidate</TableHead>
                            {judgeOrder.map((judge) => (
                                <TableHead key={judge} className="text-center text-white">
                                    {judge.replace("_", " ").toUpperCase()}
                                </TableHead>
                            ))}
                            <TableHead className="text-center text-white">Total</TableHead>
                            <TableHead className="text-center text-white">Rank</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {candidates.length === 0 && (
                            <TableRow>
                                <TableCell
                                    colSpan={judgeOrder.length + 4}
                                    className="text-center"
                                >
                                    No results available
                                </TableCell>
                            </TableRow>
                        )}

                        {candidates.map((c) => (
                            <TableRow
                                key={c.candidate.id}
                                className={
                                    c.rank === 1
                                        ? "bg-yellow-600 text-black font-bold hover:bg-yellow-500"
                                        : "bg-black text-white hover:bg-gray-900"
                                }
                            >
                                <TableCell className="bg-inherit">
                                    {c.candidate.candidate_number}
                                </TableCell>

                                <TableCell className="bg-inherit">
                                    <div className="flex items-center gap-2">
                                        <img
                                            src={
                                                c.candidate.profile_img
                                                    ? `/${c.candidate.profile_img.replace(
                                                          "admin/",
                                                          "",
                                                      )}`
                                                    : "/default-avatar.png"
                                            }
                                            alt={`${c.candidate.first_name} ${c.candidate.last_name}`}
                                            className="w-8 h-8 rounded-full object-cover"
                                        />
                                        <span>
                                            {c.candidate.first_name}{" "}
                                            {c.candidate.last_name}
                                        </span>
                                    </div>
                                </TableCell>

                                {judgeOrder.map((judge) => (
                                    <TableCell
                                        key={judge}
                                        className="text-center bg-inherit"
                                    >
                                        {Number(c.scores[judge] ?? 0).toFixed(
                                            2,
                                        )}
                                    </TableCell>
                                ))}

                                <TableCell className="text-center bg-inherit">
                                    {Number(c.total).toFixed(2)} (
                                    {(
                                        isAverageScore 
                                            ? (c.total / maxPoints) * 100
                                            : (c.total / (judgeOrder.length * maxPoints)) * 100
                                    ).toFixed(2)}
                                    %)
                                </TableCell>

                                <TableCell className="text-center bg-inherit">
                                    {c.rank}
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
};

export default ResultTable;
