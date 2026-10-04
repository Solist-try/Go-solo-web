import { Frame, PageIntro, PrimaryLink } from "@/components/gosolo/pieces";

export default function NotFound() {
  return (
    <Frame>
      <PageIntro title="This path is quiet.">
        The page is not here. The rest of the world still is.
      </PageIntro>
      <div className="mt-8">
        <PrimaryLink href="/">Return home</PrimaryLink>
      </div>
    </Frame>
  );
}
