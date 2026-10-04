import type { Metadata } from "next";
import { HomePage } from "@/components/gosolo/home";

export const metadata: Metadata = {
  title: "Go Solo",
  description:
    "Go Solo helps people build meaningful lives on their own terms. Sometimes that means a trip. Sometimes that means a quiet Tuesday. Both belong here.",
};

export default function Page() {
  return <HomePage />;
}
