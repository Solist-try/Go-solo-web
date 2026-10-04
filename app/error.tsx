"use client";

import { Frame, PageIntro, pill } from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";

export default function ErrorPage({ reset }: { error: Error; reset: () => void }) {
  return (
    <Frame>
      <PageIntro title="Something snagged.">
        The room is still here. Try once more.
      </PageIntro>
      <Button className={`${pill} mt-8`} onClick={() => reset()}>
        Try again
      </Button>
    </Frame>
  );
}
